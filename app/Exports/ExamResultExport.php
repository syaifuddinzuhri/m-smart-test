<?php

namespace App\Exports;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamClassroom;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExamResultExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents, WithCustomStartCell
{
    protected Exam $exam;
    protected Classroom $classroom;
    protected ?ExamClassroom $examClassroom;
    protected $questions;
    protected $totalQuestions;

    public function __construct($examId, $classroomId)
    {
        $this->exam = Exam::with(['subject'])->findOrFail($examId);
        $this->classroom = Classroom::with('major')->findOrFail($classroomId);
        $this->examClassroom = ExamClassroom::where('exam_id', $examId)
            ->where('classroom_id', $classroomId)
            ->first();
        $this->questions = $this->exam->questions()->orderBy('exam_questions.order')->get();
        $this->totalQuestions = $this->questions->count();
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function collection()
    {
        $sessions = ExamSession::query()
            ->select('exam_sessions.*')
            ->join('users', 'exam_sessions.user_id', '=', 'users.id')
            ->where('exam_sessions.exam_id', $this->exam->id)
            ->whereHas('user.student', fn($q) => $q->where('classroom_id', $this->classroom->id))
            ->with(['user.student', 'answers'])
            ->orderBy('users.name', 'asc')
            ->get();

        $passingGrade = $this->examClassroom?->min_total_score ?? 0;

        return $sessions->map(function ($session, $index) use ($passingGrade) {
            $answers = $session->answers->keyBy('question_id');

            $data = [
                $index + 1,
                $session->user->name,
                $session->user->student->nisn ?? '-',
                $session->user->student->gender?->getLabel() ?? '-',
            ];

            // Hasil Jawaban per nomor
            foreach ($this->questions as $q) {
                $ans = $answers->get($q->id);
                $data[] = ($ans && $ans->is_correct) ? 1 : 0;
            }

            // Kolom Tambahan Sub-Skor
            $data[] = (float) $session->score_pg;
            $data[] = (float) $session->score_short_answer;
            $data[] = (float) $session->score_essay;
            $data[] = (float) $session->total_score;

            $data[] = $session->total_score >= $passingGrade ? 'LULUS' : 'TIDAK LULUS';

            return $data;
        });
    }

    public function headings(): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 6, 'B' => 40, 'C' => 20, 'D' => 10];
        $col = 'E';

        // Width soal-soal
        for ($i = 0; $i < $this->totalQuestions; $i++) {
            $widths[$col++] = 4;
        }

        // Width 4 kolom skor (PG, JS, Essay, Total)
        $widths[$col++] = 8; // PG
        $widths[$col++] = 8; // JS
        $widths[$col++] = 8; // Essay
        $widths[$col++] = 10; // Total

        $widths[$col] = 18;   // Ket
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Total kolom: 4 (Base) + N (Soal) + 4 (Skor) + 1 (Ket)
                $totalColsCount = 4 + $this->totalQuestions + 4 + 1;
                $lastColLetter = $this->getColumnLetter($totalColsCount);
                $kkm = $this->examClassroom?->min_total_score ?? 0;

                // 1. HEADER INFORMASI
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->setCellValue('A1', 'REKAP HASIL UJIAN');
                $sheet->mergeCells("A2:{$lastColLetter}2");
                $sheet->setCellValue('A2', strtoupper($this->exam->title));
                $sheet->setCellValue('A3', 'Mata Pelajaran: ' . ($this->exam->subject->name ?? '-'));
                $sheet->setCellValue('E3', 'Kelas: ' . $this->classroom->name . ' - ' . $this->classroom->major->name);
                $sheet->setCellValue('A4', 'Skala Maksimal: ' . ($this->exam->target_max_score ?? 'Akumulasi'));
                $sheet->setCellValue('E4', 'KKM: ' . $kkm);

                // 2. TABLE HEADERS (Row 6 & 7)
                $headers = ['A6' => 'No.', 'B6' => 'Nama Lengkap', 'C6' => 'NISN', 'D6' => 'Gender'];
                foreach ($headers as $cell => $val) {
                    $sheet->setCellValue($cell, $val);
                    $sheet->mergeCells($cell . ":" . substr($cell, 0, 1) . "7");
                }

                // Header Grup: Hasil Jawaban
                $ansStartCol = 'E';
                $ansEndCol = $this->getColumnLetter(4 + $this->totalQuestions);
                $sheet->mergeCells("{$ansStartCol}6:{$ansEndCol}6");
                $sheet->setCellValue("{$ansStartCol}6", 'Hasil Jawaban (1=Benar, 0=Salah)');

                // Nomor Soal (Row 7)
                $col = 'E';
                for ($i = 1; $i <= $this->totalQuestions; $i++) {
                    $sheet->setCellValue($col . '7', $i);
                    $col++;
                }

                // Header Grup: Total Poin (PG, JS, Essay, Total)
                $scoreStartCol = $this->getColumnLetter(4 + $this->totalQuestions + 1);
                $scoreEndCol = $this->getColumnLetter(4 + $this->totalQuestions + 4);
                $sheet->mergeCells("{$scoreStartCol}6:{$scoreEndCol}6");
                $sheet->setCellValue("{$scoreStartCol}6", 'Total Poin');

                // Sub-Header Poin (Row 7)
                $sheet->setCellValue($this->getColumnLetter(4 + $this->totalQuestions + 1) . '7', 'PG');
                $sheet->setCellValue($this->getColumnLetter(4 + $this->totalQuestions + 2) . '7', 'JS');
                $sheet->setCellValue($this->getColumnLetter(4 + $this->totalQuestions + 3) . '7', 'ESSAY');
                $sheet->setCellValue($this->getColumnLetter(4 + $this->totalQuestions + 4) . '7', 'TOTAL');

                // Kolom Keterangan (Ket.)
                $statusCol = $this->getColumnLetter($totalColsCount);
                $sheet->setCellValue($statusCol . '6', 'Ket.');
                $sheet->mergeCells($statusCol . '6:' . $statusCol . '7');

                // 3. STYLING
                $styleHeader = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '444444']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ];

                $sheet->getStyle("A1:A2")->getFont()->setSize(14)->setBold(true);
                $sheet->getStyle("A1:{$lastColLetter}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A6:{$lastColLetter}7")->applyFromArray($styleHeader);

                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 8) {
                    $sheet->getStyle("A6:{$lastColLetter}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle("A8:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B8:B{$lastRow}")->getAlignment()->setIndent(1);
                    // Semua kolom data dari C (NISN) sampai akhir di-center
                    $sheet->getStyle("C8:{$lastColLetter}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }

    private function getColumnLetter($index)
    {
        $letters = '';
        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $letters = chr(65 + $remainder) . $letters;
            $index = intval(($index - $remainder) / 26);
        }
        return $letters;
    }
}
