<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');

use Dompdf\Dompdf;

$dompdf = new Dompdf();

$options = $dompdf->getOptions();
$options->setFontCache('fonts');
$options->set('isRemoteEnabled', true);
$dompdf->setOptions($options);

if (!isset($_GET['meeting_id']) || empty($_GET['meeting_id'])) {
    echo json_encode(["success" => false, "message" => "Meeting ID is required."]);
    exit;
}

$meeting_id = intval($_GET['meeting_id']);

/* =========================
   FETCH MEETING
========================= */
$query = "SELECT * FROM meetings WHERE meeting_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$meeting = $stmt->get_result()->fetch_assoc();

if (!$meeting) {
    echo json_encode(["success" => false, "message" => "Meeting not found."]);
    exit;
}

/* =========================
   FETCH AGENDA
========================= */
$agenda_query = "SELECT * FROM meeting_agenda WHERE meeting_id = ?";
$agenda_stmt = $conn->prepare($agenda_query);
$agenda_stmt->bind_param("i", $meeting_id);
$agenda_stmt->execute();
$agenda = $agenda_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* =========================
   FETCH NOTES
========================= */
$notes_query = "SELECT * FROM meeting_notes WHERE meeting_id = ?";
$notes_stmt = $conn->prepare($notes_query);
$notes_stmt->bind_param("i", $meeting_id);
$notes_stmt->execute();
$notes = $notes_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* =========================
   FETCH MEETING MINUTES (NEW)
========================= */
$meeting_minutes_query = "SELECT * FROM meeting_minutes WHERE meeting_id = ? ORDER BY created_at ASC";
$meeting_minutes_stmt = $conn->prepare($meeting_minutes_query);
$meeting_minutes_stmt->bind_param("i", $meeting_id);
$meeting_minutes_stmt->execute();
$meeting_minutes = $meeting_minutes_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* =========================
   FORMAT DATE/TIME + FILENAME
========================= */
$formatted_date = date("d M Y", strtotime($meeting['meeting_date']));
$formatted_time = date("h:i A", strtotime($meeting['meeting_time']));

$meeting_title_safe = preg_replace('/[^a-zA-Z0-9]/', '_', $meeting['meeting_title']);
$meeting_date_time = date("d_M_Y_h_i_A", strtotime($meeting['meeting_date'] . ' ' . $meeting['meeting_time']));
$filename = "{$meeting_title_safe}_{$meeting_date_time}.pdf";

/**
 * Green palette (vary strength like your previous opacity usage)
 */
$green = "#1f9d55";
$greenSoftBg = "rgba(31, 157, 85, 0.10)";
$greenLine = "rgba(31, 157, 85, 0.20)";
$greenLineLight = "rgba(31, 157, 85, 0.12)";
$greenTextSoft = "rgba(31, 157, 85, 0.85)";

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meeting Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
        }
        .container {
            margin: 20px auto;
            max-width: 800px;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px solid ' . $greenLineLight . ';
        }
        .header h2 {
            margin: 0;
            color: ' . $green . ';
            font-size: 22px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #666;
        }

        .details {
            margin: 10px 0;
            display: table;
            width: 100%;
            font-size: 16px;
            padding: 10px 15px;
            background: ' . $greenSoftBg . ';
            border-left: 5px solid ' . $green . ';
            border-radius: 0 8px 8px 0;
            color: #333;
        }
        .details span {
            display: inline-block;
            margin-right: 20px;
        }
        .details strong {
            color: ' . $greenTextSoft . ';
        }

        .agenda {
            margin-top: 10px;
        }

        /* Section titles */
        h3 {
            margin-bottom: 15px;
            color: ' . $green . ';
            font-size: 18px;
            border-bottom: 1px solid ' . $greenLineLight . ';
            padding-bottom: 8px;
        }
        .section-strong {
            margin-bottom: 15px;
            color: ' . $green . ';
            font-size: 18px;
            border-bottom: 2px solid ' . $greenLine . ';
            padding-bottom: 8px;
        }

        /* Agenda list */
        .agenda-list {
            margin: 0;
            padding-left: 0;
            list-style: none;
        }
        .agenda-list li {
            margin: 0 0 4px 0;
            padding: 0;
        }

        .agenda-item {
            border: 1px solid #fff;
            border-radius: 8px;
            padding: 4px 8px;
            background: #ffffff;
            transition: all 0.3s ease;
        }
        .agenda-item:hover {
            background: #f4f8ff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .agenda-top { display: block; }
        .agenda-left {
            display: inline-block;
            width: 78%;
            vertical-align: top;
        }
        .agenda-right {
            display: inline-block;
            width: 20%;
            vertical-align: top;
            text-align: right;
            white-space: nowrap;
        }

        /* Number beside title, both green */
        .agenda-heading {
            display: inline;
            color: ' . $green . ';
            font-weight: bold;
            font-size: 14px;
            line-height: 1.2;
        }
        .agenda-heading .num {
            display: inline;
            margin-right: 6px;
        }
        .agenda-heading .title {
            display: inline;
        }

        .agenda-time {
            font-size: 13px;
            color: #444;
        }
        .agenda-participants {
            font-size: 14px;
            color: #777;
            font-style: italic;
        }

        /* No boxes on duration */
        .agenda-duration {
            background: none;
            color: #444;
            font-size: 12px;
            padding: 0;
            border-radius: 0;
        }

        .agenda-description {
            margin-top: 4px;
            font-size: 14px;
            color: #555;
        }

        .agenda-last-item {
            margin-top: 8px;
            border-radius: 8px;
            padding: 10px 12px;
            background: #ffffff;
        }

        /* MEETING MINUTES (DISTINCT SECTION) */
        .meeting-minutes-block {
            margin-top: 14px;
            font-size: 13px;
            color: #555;
            line-height: 1.45;
        }
        .meeting-minutes-item {
            margin: 0 0 10px 0;
            padding: 10px 12px;
            border-radius: 8px;
            background: ' . $greenSoftBg . ';
            border-left: 4px solid ' . $green . ';
        }
        .meeting-minutes-meta {
            font-size: 11px;
            color: #777;
            margin-bottom: 6px;
        }
        .meeting-minutes-text {
            white-space: pre-wrap;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>' . htmlspecialchars($meeting['meeting_title']) . '</h2>
            <p>' . htmlspecialchars($meeting['meeting_description']) . '</p>
        </div>

        <div class="details">
            <span><strong>Tarehe:</strong> ' . htmlspecialchars($formatted_date) . '</span>
            <span><strong>Muda:</strong> ' . htmlspecialchars($formatted_time) . '</span>
            <span><strong>Mahali:</strong> ' . htmlspecialchars($meeting['meeting_place']) . '</span>
        </div>

        <div class="agenda">
            <h3>Agenda za Kikao</h3>';

$total_minutes = 0;

if (!empty($agenda)) {
    $html .= '<ol class="agenda-list">';

    foreach ($agenda as $index => $item) {
        $from_time = strtotime($item['from_time']);
        $to_time = strtotime($item['to_time']);

        $time_diff = $to_time - $from_time;
        $hours = floor($time_diff / 3600);
        $mins = floor(($time_diff % 3600) / 60); // renamed from $minutes to $mins (avoid confusion)
        $duration = $hours > 0 ? "$hours hr " : "";
        $duration .= $mins > 0 ? "$mins min" : "";

        $total_minutes += $hours * 60 + $mins;

        $html .= '
            <li>
                <div class="agenda-item">
                    <div class="agenda-top">
                        <div class="agenda-left">
                            <span class="agenda-heading">
                                <span class="num">' . ($index + 1) . '.</span>
                                <span class="title">' . htmlspecialchars($item['title']) . '</span>
                            </span>
                            <span class="agenda-time"> (' . htmlspecialchars(date("h:i A", $from_time)) . ' - ' . htmlspecialchars(date("h:i A", $to_time)) . ')</span>
                            <span class="agenda-participants"> | ' . htmlspecialchars($item['participants']) . '</span>
                        </div>
                        <div class="agenda-right">
                            <span class="agenda-duration">' . htmlspecialchars($duration) . '</span>
                        </div>
                    </div>
                    <div class="agenda-description">' . htmlspecialchars($item['description']) . '</div>
                </div>
            </li>';
    }

    $html .= '</ol>';

    $total_hours = floor($total_minutes / 60);
    $remaining_mins = $total_minutes % 60;
    $total_time = $total_hours > 0 ? "$total_hours hr " : "";
    $total_time .= $remaining_mins > 0 ? "$remaining_mins min" : "";

    $html .= '
        <div class="agenda-last-item">
            <div class="agenda-top">
                <div class="agenda-left">
                    <span class="agenda-heading"><strong>MUDA:</strong></span>
                </div>
                <div class="agenda-right">
                    <span class="agenda-duration"><strong>' . htmlspecialchars($total_time) . '</strong></span>
                </div>
            </div>
        </div>';
} else {
    $html .= '<p>Hakuna Agenda yoyote.</p>';
}

/* =========================
   NOTES
========================= */
if (!empty($notes)) {
    $html .= '
        <div class="notes">
            <h3 class="section-strong">Additional Notes</h3>
            <ul style="font-size: 12px; color: #666; margin: 10px 0 0; padding-left: 20px;">';

    foreach ($notes as $note) {
        $html .= '<li>' . htmlspecialchars($note['note_text']) . '</li>';
    }

    $html .= '</ul>
        </div>';
}

/* =========================
   MEETING MINUTES (DISTINCT + CLEAR LABEL)
========================= */
$has_meeting_minutes_text = false;
if (!empty($meeting_minutes)) {
    foreach ($meeting_minutes as $mm) {
        $minutesTextRaw = isset($mm["minutes_text"]) ? $mm["minutes_text"] : "";
        if (trim($minutesTextRaw) !== "") {
            $has_meeting_minutes_text = true;
            break;
        }
    }
}

if ($has_meeting_minutes_text) {
    $html .= '
        <div class="meeting-minutes-block">
            <h3 class="section-strong">Minutes za Kikao</h3>';

    foreach ($meeting_minutes as $mm) {
        $minutesTextRaw = isset($mm["minutes_text"]) ? $mm["minutes_text"] : "";
        if (trim($minutesTextRaw) === "") {
            continue;
        }

        $created = !empty($mm["created_at"]) ? date("d M Y, h:i A", strtotime($mm["created_at"])) : "";
        $updated = !empty($mm["updated_at"]) ? date("d M Y, h:i A", strtotime($mm["updated_at"])) : "";

        $metaParts = [];
        if ($created !== "") { $metaParts[] = "Imeandikwa: " . $created; }
        if ($updated !== "" && $updated !== $created) { $metaParts[] = "Imesasishwa: " . $updated; }
        $meta = implode(" | ", $metaParts);

        $html .= '
            <div class="meeting-minutes-item">
                ' . ($meta !== "" ? '<div class="meeting-minutes-meta">' . htmlspecialchars($meta) . '</div>' : '') . '
                <div class="meeting-minutes-text">' . nl2br(htmlspecialchars($minutesTextRaw)) . '</div>
            </div>';
    }

    $html .= '
        </div>';
}

$current_year = date("Y");


$html .= '
        </div>
        <div class="footer">
            <p>&copy; ' . $current_year . ' Kanisa Langu - SEWMR Technologies. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "portrait");
$dompdf->render();
$dompdf->stream($filename, ["Attachment" => false]);
?>
