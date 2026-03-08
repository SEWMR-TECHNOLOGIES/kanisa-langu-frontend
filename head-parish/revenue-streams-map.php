<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

$head_parish_id = $_SESSION['head_parish_id'];

// 1. Fetch grouped revenue streams
$query_grouped = "
SELECT 
    rg.revenue_group_name,
    rs.revenue_stream_name
FROM head_parish_revenue_groups rg
JOIN head_parish_revenue_groups_map rgm ON rg.revenue_group_id = rgm.revenue_group_id
JOIN head_parish_revenue_streams rs ON rs.revenue_stream_id = rgm.revenue_stream_id
WHERE rg.head_parish_id = ?
ORDER BY rg.revenue_group_name, rs.revenue_stream_name
";
$stmt_grouped = $conn->prepare($query_grouped);
$stmt_grouped->bind_param("i", $head_parish_id);
$stmt_grouped->execute();
$result_grouped = $stmt_grouped->get_result();

$groupedData = [];
while ($row = $result_grouped->fetch_assoc()) {
    $groupedData[$row['revenue_group_name']][] = $row['revenue_stream_name'];
}
$stmt_grouped->close();

// 2. Fetch uncategorized revenue streams
$query_uncategorized = "
SELECT revenue_stream_name 
FROM head_parish_revenue_streams 
WHERE head_parish_id = ? 
AND revenue_stream_id NOT IN (
    SELECT revenue_stream_id FROM head_parish_revenue_groups_map
)
ORDER BY revenue_stream_name
";
$stmt_uncat = $conn->prepare($query_uncategorized);
$stmt_uncat->bind_param("i", $head_parish_id);
$stmt_uncat->execute();
$result_uncat = $stmt_uncat->get_result();

$uncategorizedStreams = [];
while ($row = $result_uncat->fetch_assoc()) {
    $uncategorizedStreams[] = $row['revenue_stream_name'];
}
$stmt_uncat->close();
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Revenue Groups & Streams - Kanisa Langu');
  ?>
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require_once('components/sidebar.php') ?>
    <div class="body-wrapper">
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Revenue Groups & Their Streams</h5>

            <?php if (!empty($groupedData) || !empty($uncategorizedStreams)) : ?>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 30%;">Revenue Group</th>
                      <th>Revenue Stream</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Grouped -->
                    <?php foreach ($groupedData as $group => $streams): ?>
                      <?php foreach ($streams as $index => $stream): ?>
                        <tr>
                          <?php if ($index === 0): ?>
                            <td rowspan="<?php echo count($streams); ?>" class="align-middle fw-bold">
                              <?php echo htmlspecialchars($group); ?>
                            </td>
                          <?php endif; ?>
                          <td><?php echo htmlspecialchars($stream); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endforeach; ?>

                    <!-- Uncategorized -->
                    <?php if (!empty($uncategorizedStreams)): ?>
                      <?php foreach ($uncategorizedStreams as $index => $stream): ?>
                        <tr>
                          <?php if ($index === 0): ?>
                            <td rowspan="<?php echo count($uncategorizedStreams); ?>" class="align-middle fw-bold text-danger">
                              Uncategorized
                            </td>
                          <?php endif; ?>
                          <td><?php echo htmlspecialchars($stream); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="text-muted">No revenue streams found.</p>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once('components/footer_files.php') ?>
</body>
</html>
