<?php
$data = MonetizeUsers::getRewards(User::getId(), date('Y-m-d H:i:s', strtotime('-7 days')), date('Y-m-d H:i:s'), MonetizeUsers::$GetRewardModeGrouped);

$rows = [];
$labels = [];
foreach ($data as $value) {
    $index = "{$value['watched_date']} {$value['watched_hour']}h";
    if(empty($rows[$index])){
        $rows[$index] = array('date_hour'=>$index, 'reward'=>$value['total_reward'], 'count'=>1);
    }else{
        $rows[$index]['reward']+=$value['total_reward'];
        $rows[$index]['count']++;
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
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Watched Date</th>
                <th>Reward</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($rows as $row) {
                echo '<tr>';
                echo '<td>'.$row['date_hour'].'</td>';
                echo '<td>'.$row['reward'].'</td>';
                echo '<td>'.$row['count'].'</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

    <canvas id="rewardChart" style="width:100%; height:400px;"></canvas>
</div>

<script src="<?php echo getURL('node_modules/chart.js/dist/chart.umd.js'); ?>" type="text/javascript"></script>
<script>
let data = <?php echo json_encode($rows); ?>;
let labels = <?php echo json_encode($labels); ?>;
let rewards = <?php echo json_encode($chartData); ?>;
let counts = <?php echo json_encode($chartDataCount); ?>;

new Chart(document.getElementById("rewardChart"), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        { 
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
