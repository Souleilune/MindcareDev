<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Patient') {
  header("Location: login.php");
  exit;
}

require_once('vendor/autoload.php');
include 'supabase.php';
date_default_timezone_set('Asia/Manila');

$user_id = $_SESSION['user']['id'];
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$assessment_id) {
  echo "<script>alert('Invalid assessment ID'); window.history.back();</script>";
  exit;
}

$assessments = supabaseSelect('assessments', ['id' => $assessment_id, 'user_id' => $user_id], '*', null, null, true);

if (empty($assessments)) {
  echo "<script>alert('Assessment not found or access denied'); window.history.back();</script>";
  exit;
}

$assessment = $assessments[0];

$users = supabaseSelect('users', ['id' => $user_id], '*', null, null, true);
$user = !empty($users) ? $users[0] : [];

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('MindCare Platform');
$pdf->SetAuthor('MindCare');
$pdf->SetTitle('Mental Health Assessment Report');
$pdf->SetSubject('Assessment Results');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->AddPage();

$pdf->SetFont('helvetica', '', 10);

$primaryColor = array(90, 208, 190); 
$darkColor = array(26, 165, 146);    
$textColor = array(51, 51, 51);     
$lightGray = array(248, 249, 250); 

$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(0, 0, 210, 45, 'F');

$logoPath = 'images/Mindcare.jpg';
if (file_exists($logoPath)) {
  $pdf->Image($logoPath, 9, 8, 35, 0, '', '', '', false, 300, '', false, false, 0);
}

$pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetXY(45, 20);
$pdf->Cell(0, 8, 'MindCare', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY(45, 22);
$pdf->Cell(0, 14, 'Mental Health Assessment Report', 0, 1, 'L');

$pdf->SetLineWidth(0.2);

$pdf->SetY(50);
$pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'Patient Information', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 10);
$pdf->Ln(2);

$infoData = [
  ['Name:', $user['fullname'] ?? 'N/A'],
  ['Age:', ($user['age'] ?? 'N/A') . ' years old'],
  ['Gender:', $user['gender'] ?? 'N/A'],
  ['Email:', $user['email'] ?? 'N/A'],
  ['Phone:', $user['phone'] ?? 'Not provided'],
];

foreach ($infoData as $row) {
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(40, 6, $row[0], 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(0, 6, $row[1], 0, 1, 'L');
}

$pdf->Ln(8);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'Assessment Results', 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 8, 'Assessment Score:', 1, 0, 'L', true);
$pdf->SetFont('helvetica', '', 10);

$score = $assessment['score'];
if ($score <= 2) {
  $scoreColor = array(40, 167, 69);
  $severityText = 'Mild';
} elseif ($score <= 4) {
  $scoreColor = array(255, 193, 7);
  $severityText = 'Moderate';
} else {
  $scoreColor = array(220, 53, 69);
  $severityText = 'Severe';
}

$pdf->SetTextColor($scoreColor[0], $scoreColor[1], $scoreColor[2]);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 8, $score . '/6 (' . $severityText . ')', 1, 1, 'L', true);

$pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
$pdf->SetFont('helvetica', '', 10);

$pdf->Ln(4);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 6, 'Summary:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, $assessment['summary'] ?? 'No summary available', 0, 'L');

$pdf->Ln(4);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'Assessment Date:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 10);
$createdAtUTC = new DateTime($assessment['created_at'], new DateTimeZone('UTC'));
$createdAtPHT = $createdAtUTC->setTimezone(new DateTimeZone('Asia/Manila'));
$assessmentDate = $createdAtPHT->format('F j, Y \a\t g:i A');
$pdf->Cell(0, 6, $assessmentDate, 0, 1, 'L');

$hasDetailedResponses = false;
$detailedFields = [
  'Orientation Responses' => ['orientation_0', 'orientation_1', 'orientation_2', 'orientation_3', 'orientation_4'],
  'Emotional State' => ['emotions_0', 'emotions_1', 'emotions_2', 'emotions_3', 'emotions_4'],
  'Memory Assessment' => ['memory_initial', 'memory_recall'],
  'Thought Patterns' => ['thoughts_0', 'thoughts_1', 'thoughts_2', 'thoughts_3'],
  'Decision Making' => ['decisions_0', 'decisions_1', 'decisions_2']
];

foreach ($detailedFields as $fields) {
  foreach ($fields as $field) {
    if (!empty($assessment[$field])) {
      $hasDetailedResponses = true;
      break 2;
    }
  }
}

if ($hasDetailedResponses) {
  $pdf->Ln(8);
  $pdf->SetFont('helvetica', 'B', 14);
  $pdf->Cell(0, 8, 'Detailed Assessment Responses', 0, 1, 'L');
  $pdf->Ln(2);

  foreach ($detailedFields as $sectionTitle => $fields) {
    $hasSectionData = false;
    foreach ($fields as $field) {
      if (!empty($assessment[$field])) {
        $hasSectionData = true;
        break;
      }
    }

    if ($hasSectionData) {
      $pdf->SetFont('helvetica', 'B', 11);
      $pdf->Cell(0, 6, $sectionTitle, 0, 1, 'L');
      $pdf->Ln(1);

      $pdf->SetFont('helvetica', '', 9);
      $counter = 1;
      foreach ($fields as $field) {
        if (!empty($assessment[$field])) {
          $pdf->MultiCell(0, 5, $counter . '. ' . $assessment[$field], 0, 'L');
          $pdf->Ln(1);
          $counter++;
        }
      }
      $pdf->Ln(3);
    }
  }
}

$pdf->Ln(4);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'Recommendations', 0, 1, 'L');
$pdf->Ln(2);

$recommendations = [];
if ($score <= 2) {
  $recommendations = [
    'Continue with your current self-care practices',
    'Maintain regular sleep schedule and healthy lifestyle',
    'Consider mindfulness or meditation for stress management',
    'Stay connected with friends and family',
    'Monitor your mental health and seek help if symptoms worsen'
  ];
} elseif ($score <= 4) {
  $recommendations = [
    'Consider scheduling a consultation with a mental health specialist',
    'Practice stress-reduction techniques like deep breathing or yoga',
    'Maintain a regular sleep schedule and exercise routine',
    'Limit caffeine and alcohol consumption',
    'Reach out to trusted friends, family, or support groups',
    'Keep a journal to track your mood and identify triggers'
  ];
} else {
  $recommendations = [
    'We strongly recommend scheduling an appointment with a mental health specialist',
    'Consider speaking with your primary care physician',
    'Reach out to a crisis helpline if you\'re experiencing severe distress',
    'Avoid isolation - stay connected with supportive people',
    'Practice self-care activities that bring you comfort',
    'Consider professional therapy or counseling services'
  ];
}

$pdf->SetFont('helvetica', '', 10);
foreach ($recommendations as $index => $rec) {
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
  $pdf->Write(0, '• ', '', 0, 'L', true, 0, false, false, 0);
  $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
  $pdf->SetFont('helvetica', '', 10);
  $pdf->MultiCell(0, 5, $rec, 0, 'L');
  $pdf->Ln(2);
}

$pdf->Ln(10);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(4);

$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(128, 128, 128);
$pdf->MultiCell(0, 4, 'This assessment is for informational purposes only and does not constitute medical advice. Please consult with a qualified mental health professional for proper diagnosis and treatment.', 0, 'C');
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 4, 'Generated on ' . date('F j, Y \a\t g:i A'), 0, 1, 'C');
$pdf->Cell(0, 4, 'MindCare - Your Mental Health Partner', 0, 1, 'C');

$filename = 'Assessment_Report_' . date('Y-m-d') . '_' . $user['fullname'] . '.pdf';
$pdf->Output($filename);
exit;