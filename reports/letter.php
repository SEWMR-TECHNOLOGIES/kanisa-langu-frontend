<?php 
// This page aims at generating an income statement report
session_start();
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

use Dompdf\Dompdf;

// Instantiate Dompdf
$dompdf = new Dompdf();

// Set up the options
$options = $dompdf->getOptions();
$options->setFontCache('fonts');
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->setChroot([$_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/']);
$dompdf->setOptions($options);

$imageData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg'));
$stampData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/stamps/stamp.png'));
$pastorSignatureData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/uploads/signatures/pastor.png'));
$chairpersonSignatureData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/uploads/signatures/chairperson.png'));

// Set the correct MIME types for each
$imageSrc = 'data:image/jpeg;base64,' . $imageData;
$stampSrc = 'data:image/png;base64,' . $stampData;
$pastorSignatureSrc = 'data:image/png;base64,' . $pastorSignatureData;
$chairpersonSignatureSrc = 'data:image/png;base64,' . $chairpersonSignatureData;

$results = [];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate date
    $local_timestamp = isset($_POST['local_timestamp']) ? $conn->real_escape_string($_POST['local_timestamp']) : null; 

    // Check if a file is uploaded
    if (!isset($_FILES['harambee_data']) || $_FILES['harambee_data']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "File upload failed or no file provided"]);
        exit();
    }

    $filePath = $_FILES['harambee_data']['tmp_name'];
    $fileName = $_FILES['harambee_data']['name'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    // Validate file extension
    if (strtolower($fileExtension) !== 'xlsx') {
        echo json_encode(["success" => false, "message" => "Invalid file type. Only .xlsx files are allowed"]);
        exit();
    }

    try {
        // Use the appropriate reader for XLSX files
        $reader = new Xlsx();
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();


        // Loop through each row and extract the data
        foreach ($sheet->getRowIterator() as $row) {
             if ($row->getRowIndex() === 1) {
                continue; // Skip header row
            }

            $name = $sheet->getCell('A' . $row->getRowIndex())->getValue();
            $rawTarget = $sheet->getCell('B' . $row->getRowIndex())->getValue();
            $target = floatval(preg_replace('/[^0-9.]/', '', $rawTarget)); // Strip non-numeric, then convert to float
            $isMrMrs = $sheet->getCell('C' . $row->getRowIndex())->getValue() == 'Yes' ? true : false;
            
            // Skip row if name or target is empty
            if (empty($name) && empty($rawTarget)) {
                continue;
            }
            $results[] = [
                'name' => $name,
                'target' => $target,
                'is_mr_mrs' => $isMrMrs
            ];
            
            
        }

        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error reading the Excel file: " . $e->getMessage()]);
        exit();
    }
}

$html = '<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: helvetica;
      font-size: 11pt;
      margin: 5px 15px 15px 15px; /* top, right, bottom, left */
      color: #333;
    }
    .header {
      text-align: center;
      margin-bottom: 10px;
    }
    .header h2 {
      margin: 2px 0;
      font-size: 10pt;
      text-transform: uppercase;
      color: #2c2c2c;
    }
    .subject {
      font-weight: bold;
      margin: 20px 0 5px 0;
    }
    .content p {
      margin: 10px 0;
      line-height: 1.5;
    }
    .projects {
      margin: 10px 0;
      padding-left: 20px;
    }
    .projects ol {
      padding-left: 20px;
    }
    .projects li {
      margin-bottom: 5px;
    }
    .verse {
      font-style: italic;
      color: #444;
      margin: 10px 0;
      border-left: 3px solid #aaa;
      padding-left: 10px;
    }
    .signature {
      margin-top: 10px;
      font-weight: bold;
      font-size:12px;
    }
    .signature-container {
      position: relative;
      width: 250px; /* Adjust as needed */
      height: auto;
    }
    
    .signature-img {
      height: 30px;
      width: auto;
      display: block;
    }
    
    .stamp-img {
      position: absolute;
      top: -30;
      right: 100;
      width: 150px;
    }
    .justify{
        text-align:justify;
    }

    
  </style>
</head>';

foreach($results as $result){
    $target = number_format($result['target'], 0);
    $fisrtPronoun = ($result['is_mr_mrs']) ? 'yenu' : 'yako';
    $secondPronoun = ($result['is_mr_mrs']) ? 'wenu' : 'wako';
    $greetingLine = (!$result['is_mr_mrs'] ? 'Ndugu <strong>' . $result['name'] . '</strong>' : '<strong>' . $result['name'] . '</strong>') . ',<br>Bwana Yesu asifiwe.';

    $html .= '<body>
      <div class="header" style="text-align: center;">
          <img src="' . $imageSrc . '" alt="KKKT Logo" style="width: 60px; height: auto; margin-bottom: 5px; display: block; margin-left: auto; margin-right: auto;">
          <h2>KKKT DAYOSISI YA KASKAZINI KATI</h2>
          <h2>JIMBO LA ARUSHA MASHARIKI | USHARIKA WA ELERAI</h2>
    </div>
    
      <p>'.$greetingLine.'</p>
    
      <div class="subject">YAH: SHUKURANI KWA UTUMISHI '.strtoupper($secondPronoun).' NA OMBI MAALUMU</div>
    
      <div class="content">
        <p class="justify">Tunamshukuru Mungu kwa ajili '.$fisrtPronoun.' na kwa mchango '.$secondPronoun.' mkubwa katika harambee iliyopita ya miradi ya maendeleo ya Usharika wetu. Ushiriki '.$secondPronoun.' ulileta mafanikio makubwa, na tunatambua mchango '.$secondPronoun.' kama sehemu ya msingi wa hatua tulizopiga.</p>
    
        <p class="justify">Tunajiandaa kwa harambee nyingine yenye lengo la kukusanya Shilingi <strong>219,859,585/=</strong>, itakayozinduliwa rasmi tarehe <strong>4 Mei 2025</strong> na kilele <strong>07 Septemba 2025</strong>. Tunakualika kwa upendo kuhudhuria ibada ya 1 au ya 2 siku hiyo. Kwa heshima na upendo, tunakuomba kuchangia Shilingi <strong>'. $target.'/=</strong> au zaidi, kwa kadri Mungu atakavyokujalia, kama sehemu ya juhudi hizi muhimu.</p>
    
        <p>Miradi ya maendeleo inayotarajiwa kutekelezwa ni kama ifuatavyo:</p>
    
        <div class="projects">
          <ol>
            <li>Kuweka fensi usoni mwa Kanisa, grili na mageti.</li>
            <li>Kuweka Alcobond mbele na upande wa mashariki mwa Kanisa.</li>
            <li>Kuweka mosaic kwenye nguzo za mbele ya Kanisa.</li>
            <li>Kuandaa Master Plan ya Kanisa.</li>
            <li>Kuweka vioo vya madhabahuni.</li>
            <li>Kutengeneza mchoro wa Kanisa letu (3D).</li>
            <li>Kununua samani za ofisi.</li>
            <li>Kuondoa mapungufu ya bajeti ya miradi 2024.</li>
            <li>Kujenga fensi katika eneo jipya tulilonunua.</li>
            <li>Kufunga kamera za CCTV.</li>
            <li>Kununua drums.</li>
            <li>Kujenga chumba cha drums.</li>
            <li>Kuondoa mapungufu ya bajeti ya uendeshaji.</li>
            <li>Kununua gari la Kwaya ya Jeshi la Bwana.</li>
          </ol>
        </div>
    
        <p>Tunaamini kwamba kwa moyo '.$secondPronoun.' wa upendo na kujitolea, mchango '.$secondPronoun.' utasaidia sana kufanikisha lengo hili. Tunakuombea baraka tele na mafanikio katika maisha '.$fisrtPronoun.'.</p>
      </div>
    
      <div class="verse">
        “Mungu njia yake ni kamilifu; Ahadi ya BWANA imehakikishwa; Yeye ndiye ngao yao Wote wanaomkimbilia.” (2 Samweli 22:31)
      </div>
    
    <table style="width: 100%;">
      <tr>
        <td style="width: 50%; vertical-align: top; font-size: 12px; font-weight: bold;">
          <p>Wako katika huduma ya Bwana,</p>
          <div style="position: relative; height: 40px;">
            <img src="' . $pastorSignatureSrc . '" alt="Signature" style="height: 40px;">
            <img src="' . $stampSrc . '" alt="Stamp" style="position: absolute; top: -20px; right: 120px; width: 180px;">
          </div>
          Lonin\'go L. Sindini<br>
          Mchungaji Kiongozi<br>
          0754845163
        </td>
    
        <td style="width: 50%; vertical-align: top; font-size: 12px; font-weight: bold;">
          <p>&nbsp;</p> <!-- Keeps alignment with pastor section -->
          <div style="height: 40px;">
            <img src="' . $chairpersonSignatureSrc . '" alt="Chairperson Signature" style="height: 30px;">
          </div>
          Ephraem L. Maina<br>
          Mwenyekiti wa Harambee<br>
          0754476129
        </td>
      </tr>
    </table>
    </body>';
}

$html .='</html>';

$fileName = $_SERVER['DOCUMENT_ROOT'] . '/logs/mchango_harambee_' . $local_timestamp . '.pdf';

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Save the generated PDF to a file
$output = $dompdf->output();
file_put_contents($fileName, $output);

// Construct the download URL
$downloadUrl = '/logs/mchango_harambee_' . $local_timestamp . '.pdf';

// Return the response with the download URL
echo json_encode([
    "success" => true,
    "message" => "Letters generated successfully!",
    "data" => $results, 
    "download_url" => $downloadUrl // URL to the saved PDF
]);
?>
