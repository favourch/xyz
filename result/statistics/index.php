<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,2,3,4,5,6,20,24";
check_auth($auth_users, $site_root);

$sesid = -1;
$crsid = '';
$depid = -1;

$data = array();

if(isset($_GET['sesid'])){
    $sesid = $_GET['sesid'];
}

if(isset($_GET['crsid'])){
    $crsid = $_GET['crsid'];
}

if(isset($_GET['depid'])){
    $depid = $_GET['depid'];
}

//get Course details 
$scDetailsSQL = sprintf("SELECT csid, csname "
                        . "FROM course "
                        . "WHERE csid = %s ", 
                        GetSQLValueString($crsid, 'text'));
$csDetailsRS = mysql_query($scDetailsSQL) or die(mysql_error());
$csDetailsRow = mysql_fetch_assoc($csDetailsRS);


//Get Result 
$resultSQL = sprintf("SELECT r.stdid,(r.escore + r.tscore) AS total, s.sesname "
                    . "FROM result r, session s "
                    . "WHERE r.csid = %s AND s.sesid = r.sesid "
                    . "AND r.sesid = %s ", 
                    GetSQLValueString($crsid, 'text'), 
                    GetSQLValueString($sesid, 'int')); 
$resultRS = mysql_query($resultSQL) or die(mysql_error());
$resultRow = mysql_fetch_assoc($resultRS);
$resultNumRows = mysql_num_rows($resultRS);

if($resultNumRows > 0){
    do{
        $r = array();
        $r['stdid']    = $resultRow['stdid'];
        $r['score']    = (int)$resultRow['total'];
            
        array_push($data, $r);
    }while($resultRow = mysql_fetch_assoc($resultRS));
}


?>
<!doctype html>
<html >
<?php include INCPATH . "/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed"  >
<?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
        <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>
                    <div class="row-fluid">                        
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                         Result Statistics for <?= $csDetailsRow['csid']?> Session (<?= mysql_result($resultRS, 0, 'sesname');?>)
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="span9">
                                            <div class="well">
                                                <h3>Bar Chart</h3>
                                                <canvas id="canvas"></canvas>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <div class="well">
                                               <h3>Summary</h3>
                                               <table class="table">
                                                   <tr>
                                                       <td>Choose a Range</td>
                                                       <td>
                                                            <select id="interval" class="input input-small"  onchange="changeInterval(this)" >
                                                                <option value="1">5</option>
                                                                <option value="2" selected="selected">10</option>
                                                            </select>
                                                       </td>
                                                   </tr>
                                               </table>
                                               <table class="table table-bordered table-condensed table-striped" >
                                                   <thead>
                                                       <tr>
                                                           <th>Score Range</th>
                                                           <th>No. of Students</th>
                                                           <th>Cum</th>
                                                       </tr>
                                                   </thead>
                                                   <tbody id="summaryOfResults"></tbody>
                                               </table>
                                            </div>
                                        </div>
                                    </div>                     
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            
            <?php include INCPATH . "/footer.php" ?>
        </div>
        
        <script type="text/javascript">
            var dt  = <?= json_encode($data)?>;
        </script>
        <script src="lib/Chart.bundle.min.js"></script>
        <style>
        canvas {
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
        }
        </style>
        
        <script>
            
        var MONTHS = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        var randomScalingFactor = function() {
            return (Math.random() > 0.5 ? 1.0 : -1.0) * Math.round(Math.random() * 100);
        };
        var randomColorFactor = function() {
            return Math.round(Math.random() * 255);
        };
        var randomColor = function() {
            return 'rgba(' + randomColorFactor() + ',' + randomColorFactor() + ',' + randomColorFactor() + ',.7)';
        };
        
        

        var barChartData = {
            labels: ["0-9", "10-19", "20-29", "30-39", "40-49", "50-59", "60-69","70-79", "80-89", "90-100"],
            datasets: [{
                label: 'Total Students',
                backgroundColor: "rgba(220,220,220,0.5)",
                data: [0,0,0,0,0,0,0,0,0,0]
            }]

        };

        

        $('#randomizeData').click(function() {
            var zero = Math.random() < 0.2 ? true : false;
            $.each(barChartData.datasets, function(i, dataset) {
                dataset.backgroundColor = randomColor();
                dataset.data = dataset.data.map(function() {
                    return zero ? 0.0 : randomScalingFactor();
                });

            });
            window.myBar.update();
        });

        $('#addDataset').click(function() {
            var newDataset = {
                label: 'Dataset ' + barChartData.datasets.length,
                backgroundColor: randomColor(),
                data: []
            };

            for (var index = 0; index < barChartData.labels.length; ++index) {
                newDataset.data.push(randomScalingFactor());
            }

            barChartData.datasets.push(newDataset);
            window.myBar.update();
        });

        $('#addData').click(function() {
            if (barChartData.datasets.length > 0) {
                var month = MONTHS[barChartData.labels.length % MONTHS.length];
                barChartData.labels.push(month);

                for (var index = 0; index < barChartData.datasets.length; ++index) {
                    //window.myBar.addData(randomScalingFactor(), index);
                    barChartData.datasets[index].data.push(randomScalingFactor());
                }

                window.myBar.update();
            }
        });

        $('#removeDataset').click(function() {
            barChartData.datasets.splice(0, 1);
            window.myBar.update();
        });

        $('#removeData').click(function() {
            barChartData.labels.splice(-1, 1); // remove the label first

            barChartData.datasets.forEach(function(dataset, datasetIndex) {
                dataset.data.pop();
            });

            window.myBar.update();
        });
        
        
            
        var angularData;
        var cum = {};

        
        function loadGraph10(){
            //declear Dataset data
            var a1 = 0;
            var b1 = 0;
            var c1 = 0;
            var d1 = 0;
            var e1 = 0;
            var f1 = 0;
            var g1 = 0;
            var h1 = 0;
            var i1 = 0;
            var j1 = 0;
            
            var a2 = 0;
            var b2 = 0;
            var c2 = 0;
            var d2 = 0;
            var e2 = 0;
            var f2 = 0;
            var g2 = 0;
            var h2 = 0;
            var i2 = 0;
            var j2 = 0;
            
            for(var idx = 0; idx < dt.length; idx++){
                    if(0 < parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 9 ){
                        a1 = a1 + 1;  
                    }
                    if(10 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 19 ){
                        b1=b1 +1;  
                    }
                    if(20 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 29 ){
                        c1 = c1 +1;  
                    }
                    if(30 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 39 ){
                        d1 = d1 +1;  
                    }
                    if(40 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 49 ){
                        e1 = e1 +1;  
                    }
                    if(50 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 59 ){
                        f1 = f1 +1;  
                    }
                    if(60 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 69 ){
                        g1 = g1 +1;  
                    }
                    if(70 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 79 ){
                        h1 = h1 +1;  
                    }
                    if(80 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 89 ){
                        i1 = i1 +1;  
                    }
                    if(90 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 100 ){
                        j1 = j1 +1;  
                    }
               }
               
               a2 = a1;
            b2 = b1 + a2;
            c2 = c1 + b2;
            d2 = d1 + c2;
            e2 = e1 + d2;
            f2 = f1 + e2;
            g2 = g1 + f2;
            h2 = h1 + g2;
            i2 = i1 + h2;
            j2 = j1 + i2;
            
            
            barChartData.labels = ["0-9", "10-19", "20-29", "30-39", "40-49", "50-59", "60-69","70-79", "80-89", "90-100"];
            barChartData.datasets[0].data = [a1, b1, c1, d1, e1, f1, g1, h1, i1, j1];
            cum.data = [a2, b2, c2, d2, e2, f2, g2, h2, i2,j2];
            barChartData.datasets[0].backgroundColor = randomColor();
            window.myBar.update();
            loopForAngular(barChartData);
        };
        
        function loadGraph5(){
            var a1 = 0;
            var b1 = 0;
            var c1 = 0;
            var d1 = 0;
            var e1 = 0;
            var f1 = 0;
            var g1 = 0;
            var h1 = 0;
            var i1 = 0;
            var j1 = 0;
            var k1 = 0;
            var l1 = 0;
            var m1 = 0;
            var n1 = 0;
            var o1 = 0;
            var p1 = 0;
            var q1 = 0;
            var r1 = 0;
            var s1 = 0;
            var t1 = 0;
            
            var a2 = 0;
            var b2 = 0;
            var c2 = 0;
            var d2 = 0;
            var e2 = 0;
            var f2 = 0;
            var g2 = 0;
            var h2 = 0;
            var i2 = 0;
            var j2 = 0;
            var k2 = 0;
            var l2 = 0;
            var m2 = 0;
            var n2 = 0;
            var o2 = 0;
            var p2 = 0;
            var q2 = 0;
            var r2 = 0;
            var s2 = 0;
            var t2 = 0;
            
            for(var idx = 0; idx < dt.length; idx++){
                if(0 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 5 ){
                    a1 = a1 + 1;    
                }
                if(5 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 10 ){
                    b1=b1 +1;
                }
                if(10 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 15 ){
                    c1 = c1 +1; 
                }
                if(15 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 20 ){
                    d1 = d1 +1;
                }
                if(20 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 25 ){
                    e1 = e1 +1;  
                }
                if(25 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 30 ){
                    f1 = f1 +1; 
                }
                if(30 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 35 ){
                    g1 = g1 +1; 
                }
                if(35 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 40 ){
                    h1 = h1 + 1;
                }
                if(40 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 45 ){
                    i1 = i1 +1; 
                }
                if(45 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 50 ){
                    j1 = j1 +1;
                }
                if(50 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 55 ){
                    k1=k1 +1;
                }
                if(55 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 60 ){
                    l1 = l1 +1; 
                }
                if(60 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 65 ){
                    m1 = m1 +1;
                }
                if(65 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 70 ){
                    n1 = n1 +1;
                }
                if(70 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 75 ){
                    o1 = o1 +1;
                }
                if(75 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 80 ){
                    p1 = p1 +1; 
                }
                if(81 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 85 ){
                    q1 = q1 +1;
                }
                if(85 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 90 ){
                    r1 = r1 +1;  
                }
                if(90 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) < 95 ){
                    s1 = s1 + 1; 
                }
                if(95 <= parseInt(dt[idx].score) && parseInt(dt[idx].score) <= 100 ){
                    t1 = t1 +1;  
                }
           }
            a2 = a1;
            b2 = b1 + a2;
            c2 = c1 + b2;
            d2 = d1 + c2;
            e2 = e1 + d2;
            f2 = f1 + e2;
            g2 = g1 + f2;
            h2 = h1 + g2;
            i2 = i1 + h2;
            j2 = j1 + i2;
            k2 = k1 + j2;
            l2 = l1 + k2;
            m2 = m1 + l2;
            n2 = n1 + m2;
            o2 = o1 + n2;
            p2 = p1 + o2;
            q2 = q1 + p2;
            r2 = r1 + q2;
            s2 = s1 + r2;
            t2 = t1 + s2;
            barChartData.labels = ["0-4", "5-9", "10-14", "15-19", "20-24", "25-29", "30-34", "35-39", "40-44", "45-49", "50-54","55-59", "60-64", "65-69", "70-74", "75-79", "80-84", "85-89","90-94", "95-100"];
            barChartData.datasets[0].data = [a1, b1, c1, d1, e1, f1, g1, h1, i1,j1, k1,l1,m1,n1,o1,p1,q1,r1,s1,t1];
            cum.data = [a2, b2, c2, d2, e2, f2, g2, h2, i2,j2, k2, l2, m2, n2, o2, p2, q2, r2, s2, t2];
            barChartData.datasets[0].backgroundColor = randomColor();
            window.myBar.update();
            loopForAngular(barChartData);  
        }
        
        
        function changeInterval(elem){
            var selectedOption = elem.options[elem.selectedIndex].value;
            
           if(selectedOption == 2){
                loadGraph10();
           }
           
           if(selectedOption == 1){
                loadGraph5();    
           }
           
            
            
       }
        
        window.onload = function() {
            var ctx = document.getElementById("canvas").getContext("2d");
            window.myBar = new Chart(ctx, {
                type: 'bar',
                data: barChartData,
                options: {
                    // Elements options apply to all of the options unless overridden in a dataset
                    // In this case, we are setting the border of each bar to be 2px wide and green
                    elements: {
                        rectangle: {
                            borderWidth: 2,
                            borderColor: 'rgb(0, 255, 0)',
                            borderSkipped: 'bottom'
                        }
                    },
                    responsive: true,
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: ''
                    }
                }
            });
            loadGraph10();
        };
        
        function loopForAngular(rc){
            var url = window.location.href
            var queryString = url.split('?')[1];
            
            var table = $('#summaryOfResults');
            table.empty();
            //table.childNodes = new Array();
            var td;
            for(var i=0; i < rc.labels.length ; i++){
                table.append('<tr>');
                td = "<th>"+rc.labels[i]+"</th>" + "<td> <a target='tab' href='stat_list.php?"+queryString+"&rng="+rc.labels[i]+"'>"+rc.datasets[0].data[i]+"</a></td><td>"+cum.data[i]+"</td>";
                table.append(td);
                table.append('</tr>');
            }
            
        }
        
        
    </script>
    
    </body>
</html>