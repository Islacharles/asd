<?php
session_start(); // Start the session
include '../../Config/connection.php';
include '../../Config/auth.php';

require '../../vendor/autoload.php';// Load PhpSpreadsheet library
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch all student data and group them by grade and section
    $query = $conn->prepare("
    SELECT DISTINCT 
        s.id AS student_id, 
        CONCAT(s.FIRSTNAME, ' ', s.LASTNAME) AS student_name, 
        s.GRADE_LEVEL, 
        s.SECTION, 
        g.FIRSTNAME AS guardian_firstname, 
        g.LASTNAME AS guardian_lastname, 
        g.CONTACT_NUMBER AS guardian_contact, 
        g.EMAIL AS guardian_email
    FROM 
        students s
    LEFT JOIN 
        student_guardian sg ON s.id = sg.student_number -- Join with student_guardian table
    LEFT JOIN 
        guardian g ON sg.parent_id = g.id -- Join with guardian table using parent_id
    ORDER BY 
        s.GRADE_LEVEL ASC, s.SECTION ASC
    ");

    $query->execute();
    $result = $query->get_result();

    // Initialize Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Excel Title
    $sheet->setCellValue('A1', 'Student Records');
    $sheet->setCellValue('A2', 'Grouped by Grade and Sorted by Section');

    // Apply Title Styling
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A2')->getFont()->setSize(12);
    $sheet->mergeCells('A1:G1');
    $sheet->mergeCells('A2:G2');
    $sheet->getStyle('A1:G2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Group students by grade
    $students_by_grade = [];
    while ($data = $result->fetch_assoc()) {
        $students_by_grade[$data['GRADE_LEVEL']][] = $data;
    }

    // Set the row for writing the header, starting below the title
    $row = 4;

    // Loop through each grade level to generate a table for each grade
    foreach ($students_by_grade as $grade_level => $students) {
        // Set title for each grade
        $sheet->setCellValue('A' . $row, 'Grade ' . $grade_level);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->getStyle("A{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E2F3');
        $row++;

        // Table Headers
        $headers = ['Student #', 'Full Name', 'Grade Level', 'Section', 'Guardian Name', 'Contact Number', 'Email'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . $row, $header);
            $sheet->getStyle($column . $row)->getFont()->setBold(true);
            $sheet->getStyle($column . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($column . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($column . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
            $column++;
        }

        $row++;

        // Populate the data for each student in the grade
        foreach ($students as $student) {
            $studentFullName = $student['student_name'];
            $guardianFullName = $student['guardian_firstname'] . ' ' . $student['guardian_lastname'];

            // Render each student's details in the rows
            $sheet->setCellValue('A' . $row, $student['student_id']);
            $sheet->setCellValue('B' . $row, $studentFullName);
            $sheet->setCellValue('C' . $row, $student['GRADE_LEVEL']);
            $sheet->setCellValue('D' . $row, $student['SECTION']);
            $sheet->setCellValue('E' . $row, $guardianFullName);
            $sheet->setCellValue('F' . $row, $student['guardian_contact']);
            $sheet->setCellValue('G' . $row, $student['guardian_email']);

            // Apply borders and alternate row colors
            foreach (range('A', 'G') as $col) {
                $sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9F9F9');
            }
            $row++;
        }

        // Add some space between grade tables
        $row++;
    }

    // Set Auto Column Widths
    foreach (range('A', 'G') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Set Headers for Excel Download
    $fileName = "Student_Records_Grouped_By_Grade.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // Correct MIME type
    header('Content-Disposition: attachment; filename="' . $fileName . '"'); // Set file name with .xlsx extension

    // Write and Export File
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
?>
