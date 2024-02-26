<?php
$videosData = [];
foreach ($data as $value) {
    $videosId = $value['videos_id'];
    $reward = $value['total_reward'];
    $title = $value['title'];

    // If this videos_id is already in the $videosData array, update its reward, otherwise add it as a new entry.
    if (isset($videosData[$videosId])) {
        $videosData[$videosId]['reward'] += $reward;
        $videosData[$videosId]['count']++;
    } else {
        $videosData[$videosId] = [
            'reward' => $reward,
            'title' => $title,
            'count' => 1,
        ];
    }
}

$videosIds = [];
$rewards = [];
$titles = [];
foreach ($videosData as $videosId => $videoInfo) {
    $videosIds[] = $videosId;
    $rewards[] = $videoInfo['reward'];
    $titles[] = $videoInfo['title'];
}
?>

<div class="container">
    <div class="panel panel-default">
        <div class="panel-body">
            <canvas id="rewardChart2" style="width:100%; height:400px;"></canvas>
        </div>
    </div>

    
    <div class="panel panel-default">
      <div class="panel-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Video</th>
              <th>Reward</th>
              <th>Views</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($videosData as $row) {
              echo '<tr>';
              echo '<td>' . $row['title'] . '</td>';
              echo '<td>' . YPTWallet::formatCurrency($row['reward']) . '</td>';
              echo '<td>' . $row['count'] . '</td>';
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
</div>

<script>
    let videosIds = <?php echo json_encode($videosIds); ?>;
    let rewards2 = <?php echo json_encode($rewards); ?>;
    let titles = <?php echo json_encode($titles); ?>;

    new Chart(document.getElementById("rewardChart2"), {
        type: 'bar',
        data: {
            labels: titles, // Use the video titles as labels
            datasets: [{
                data: rewards2,
                label: "Money Made",
                backgroundColor: "#3e95cd",
            }]
        },
        options: {
            title: {
                display: true,
                text: 'Money Made per Video'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                    }
                }]
            }
        }
    });
</script>
