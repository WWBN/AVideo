<?php
$rows = [];
$labels = [];
foreach ($data as $value) {
  $index = "{$value['watched_date']} {$value['watched_hour']}h";
  if (empty($rows[$index])) {
    $rows[$index] = array('date_hour' => $index, 'reward' => $value['total_reward'], 'count' => 1, 'data' => array($value));
  } else {
    $rows[$index]['reward'] += $value['total_reward'];
    $rows[$index]['count']++;
    $rows[$index]['data'][] = $value;
  }
}

$chartData = [];
$chartDataCount = [];
foreach ($rows as $key => $value) {
  $labels[] = $key;
  $chartData[] = $value['reward'];
  $chartDataCount[] = $value['count'];
}
?>
<div class="container">
  <div class="panel panel-default">
    <div class="panel-heading" style="height: 70px;">
      <h1><?php echo __('Reward'); ?></h1>
    </div>
    <div class="panel panel-default">
      <div class="panel-body">
        <canvas id="rewardChart" style="width:100%; height:400px;"></canvas>
      </div>
      <div class="panel-footer">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Watched Date</th>
              <th>Reward</th>
              <th>Views</th>
              <th>Videos</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $reversedRows = array_reverse($rows);

            foreach ($reversedRows as $row) {
              echo '<tr>';
              echo '<td>' . $row['date_hour'] . '</td>';
              echo '<td>' . YPTWallet::formatCurrency($row['reward']) . '</td>';
              echo '<td>' . $row['count'] . '</td>';
              echo '<td>';
              $records = array();
              foreach ($row['data'] as $key => $recordData) {
                $link = Video::getLinkToVideo($recordData['videos_id']);
                $records[] = "" . ($key + 1) . " - <a href=\"{$link}\" target=\"_blank\">" . strip_tags($recordData['title']) . "</a>";
              }
              echo implode('<br>', $records);
              echo '</td>';
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
  let data = <?php echo json_encode($rows); ?>;
  let labels = <?php echo json_encode($labels); ?>;
  let rewards = <?php echo json_encode($chartData); ?>;
  let counts = <?php echo json_encode($chartDataCount); ?>;

  new Chart(document.getElementById("rewardChart"), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
          data: rewards,
          label: "Reward",
          borderColor: "#3e95cd",
          fill: false,
          yAxisID: 'y-axis-reward'
        },
        {
          data: counts,
          label: "Count",
          borderColor: "#8e5ea2",
          fill: false,
          yAxisID: 'y-axis-count'
        }
      ]
    },
    options: {
      title: {
        display: true,
        text: 'Rewards and count over time'
      },
      scales: {
        'y-axis-reward': {
          type: 'linear',
          display: true,
          position: 'left'
        },
        'y-axis-count': {
          type: 'linear',
          display: true,
          position: 'right'
        }
      }
    }
  });
</script>