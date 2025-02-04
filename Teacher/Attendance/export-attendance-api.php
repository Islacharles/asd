<?php
require '../../vendor/autoload.php'; // Load PhpSpreadsheet library

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];

    include '../../Config/connection.php';

    // Fetch attendance data
        $query = $conn->prepare("
        SELECT 
            sa.student_id AS student_number, 
            s.FIRSTNAME AS student_firstname, 
            s.LASTNAME AS student_lastname, 
            s.GRADE_LEVEL, 
            s.SECTION, 
            sa.TIME_IN, 
            sa.TIME_OUT, 
            ap.FIRSTNAME AS authorize_firstname, 
            ap.LASTNAME AS authorize_lastname
        FROM 
            student_attendance sa
        JOIN 
            students s 
        ON 
            sa.student_id = s.id
        LEFT JOIN 
            (
                SELECT 
                    ID, 
                    FIRSTNAME, 
                    LASTNAME 
                FROM 
                    authorize_person 
                UNION 
                SELECT 
                    ID, 
                    FIRSTNAME, 
                    LASTNAME 
                FROM 
                    guardian
            ) ap 
        ON 
            sa.AUTHORIZE_ID = ap.ID
        WHERE 
            sa.CREATED_DT = ? 
        ORDER BY 
            s.GRADE_LEVEL ASC, 
            s.SECTION ASC
    ");

    $query->bind_param('s', $date);
    $query->execute();
    $result = $query->get_result();


    // Initialize Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Excel Title
    $sheet->setCellValue('A1', 'Attendance Report');
    $sheet->setCellValue('A2', 'Date: ' . $date);
    
    // Apply Title Styling
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A2')->getFont()->setSize(12);
    $sheet->mergeCells('A1:G1');
    $sheet->mergeCells('A2:G2');
    $sheet->getStyle('A1:G2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Initialize variables to group students by grade
    $students_by_grade = [];

    while ($data = $result->fetch_assoc()) {
        // Group students by grade level
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
        $headers = ['Student #', 'Full Name', 'Grade Level', 'Section', 'Time In', 'Time Out', 'Authorize Person'];
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
        $rowIndex = $row;
        foreach ($students as $student) {
            $studentFullName = $student['student_firstname'] . ' ' . $student['student_lastname'];
            $authorizeFullName = $student['authorize_firstname'] . ' ' . $student['authorize_lastname'];

            $sheet->setCellValue('A' . $rowIndex, $student['student_number']);
            $sheet->setCellValue('B' . $rowIndex, $studentFullName);
            $sheet->setCellValue('C' . $rowIndex, $student['GRADE_LEVEL']);
            $sheet->setCellValue('D' . $rowIndex, $student['SECTION']);
            $sheet->setCellValue('E' . $rowIndex, $student['TIME_IN']);
            $sheet->setCellValue('F' . $rowIndex, $student['TIME_OUT']);
            $sheet->setCellValue('G' . $rowIndex, $authorizeFullName);

            // Style the row for better readability
            $sheet->getStyle('A' . $rowIndex . ':G' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $rowIndex . ':G' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Alternating row colors for better readability
            if ($rowIndex % 2 == 0) {
                $sheet->getStyle('A' . $rowIndex . ':G' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9F9F9');
            }

            $rowIndex++;
        }

        // Add some space between grade tables
        $rowIndex++;
        $row = $rowIndex;
    }

    // Set Auto Column Widths
    foreach (range('A', 'G') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Set Headers for Excel Download
    $fileName = "Attendance_Report_$date.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // Correct MIME type
    header('Content-Disposition: attachment; filename="' . $fileName . '"'); // Set file name with .xlsx extension

    // Write and Export File
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
?>
