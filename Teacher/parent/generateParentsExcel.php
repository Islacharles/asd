<?php
require '../../vendor/autoload.php'; // Include PhpSpreadsheet library
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Database connection
include '../../Config/connection.php';

// Fetch data from the database
$query = "SELECT 
            g.ID as 'PARENT_NUMBER',
            CONCAT(g.FIRSTNAME, ' ', g.LASTNAME) AS FullName, 
            CONCAT(g.FIRSTNAME, g.LASTNAME) AS defaultPword,
            g.CONTACT_NUMBER, 
            g.EMAIL,
            g.PICTURE,
            (SELECT COUNT(*) FROM student_guardian WHERE PARENT_ID = g.ID) AS TOTAL_STUDENTS
          FROM 
            guardian AS g
          WHERE 
            CONCAT(g.FIRSTNAME, ' ', g.LASTNAME) LIKE ?";
$stmt = $conn->prepare($query);

$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchParam = "%$search%";
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the title/header of the sheet
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', 'Parents List Report');
$titleStyle = [
    'font' => [
        'bold' => true,
        'size' => 16,
        'color' => ['rgb' => '000000'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];
$sheet->getStyle('A1:F1')->applyFromArray($titleStyle);

// Add a sub-header with additional information
$sheet->mergeCells('A2:F2');
$dateGenerated = date('Y-m-d H:i:s');
$sheet->setCellValue('A2', "Generated on: $dateGenerated");
$sheet->getStyle('A2:F2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Leave a blank row before the main table
$startRow = 4;

// Set header row for the table
$headers = ['Parent Number', 'Full Name', 'Default Password', 'Contact Number', 'Email', 'Total Students'];
$sheet->fromArray($headers, NULL, "A$startRow");

// Apply styles to the header row
$headerStyle = [
    'font' => [
        'bold' => true,
        'size' => 12,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4CAF50']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
];
$sheet->getStyle("A$startRow:F$startRow")->applyFromArray($headerStyle);

// Auto-size columns
foreach (range('A', 'F') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Populate data rows
$rowNum = $startRow + 1;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue("A$rowNum", $row['PARENT_NUMBER']);
    $sheet->setCellValue("B$rowNum", $row['FullName']);
    $sheet->setCellValue("C$rowNum", $row['defaultPword']);
    $sheet->setCellValue("D$rowNum", $row['CONTACT_NUMBER']);
    $sheet->setCellValue("E$rowNum", $row['EMAIL']);
    $sheet->setCellValue("F$rowNum", $row['TOTAL_STUDENTS']);
    $rowNum++;
}

// Add borders to the data rows
$dataStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];
$sheet->getStyle("A$startRow:F$rowNum")->applyFromArray($dataStyle);

// Freeze the header row
$sheet->freezePane("A" . ($startRow + 1));

// Output to Excel file
$filename = 'Parents_List_Report.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=$filename");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
