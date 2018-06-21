<?php
require_once('../../path.php');

$url_part = '';

if(isset($_GET['action'])){
    $url_part = $_GET['action'];
}

$post = '';
if(isset($_POST)){
$post = json_decode(file_get_contents('php://input'));

}

$data = array();
$limit1 = "";
$limit = "";
$offset = "";
$page = "";

if (isset($_GET['page']) || isset($_GET['limit'])) {
    //pls validate that are numbers
    $page = (isset($_GET['page']) && $_GET['page'] > 0) ? (int) $_GET['page'] : 1;
    $limit1 = isset($_GET['limit']) ? "LIMIT " . (int) $_GET['limit'] : "LIMIT " . 15;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 15;

    $offset = "OFFSET " . ( --$page) * $limit;

//    $query1 = "SELECT COUNT(postid) AS total FROM market_post mp";
//    $postTotalRS = mysql_query($query1, $tams) or die(mysql_error());
//    $post_total_row = mysql_fetch_assoc($postTotalRS);
//    $data['total_page'] = ceil((int)$post_total_row['total'] / $limit);

}


switch ($url_part){
    case 'post':
        $postSQL = sprintf("SELECT * FROM market_post mp "
                        . "JOIN market_cat mc "
                        . "ON mp.post_cat = mc.catid "
                        . "JOIN market_seler ms "
                        . "ON ms.id = mp.seler_id %s %s", 
                        $limit1, $offset);
        $postRS = mysql_query($postSQL, $tams) or die(mysql_error());
        $post_row = mysql_fetch_assoc($postRS);
        $post_num_row = mysql_num_rows($postRS);


        $initial_data = array();
        do {
            $initial_data['rs'] = $post_row;

            $postImgSQL = sprintf("SELECT * "
                                . "FROM market_post_img "
                                . "WHERE postid = %s "
                                . "LIMIT 1 ", 
                                GetSQLValueString($post_row['postid'], 'int'));
            $postImgRS = mysql_query($postImgSQL, $tams) or die(mysql_error());
            $post_Img_row = mysql_fetch_assoc($postImgRS);

            $initial_data['img'] = $post_Img_row['img_url'];

            array_push($data, $initial_data);
        } while ($post_row = mysql_fetch_assoc($postRS));
        header("Content-Type: application/json");
        echo json_encode($data);
        
        break;
        
    case 'send_reviews':
        $query_update1  = sprintf("INSERT "
                                . "INTO market_review "
                                . "( post_id, revhead, comment, created_at ) "
                                . "VALUES(%s, %s, %s, %s)",
                                GetSQLValueString($post->postid, 'int'),
                                GetSQLValueString($post->who, 'text'),
                                GetSQLValueString($post->comment, 'text'),
                                GetSQLValueString(date('Y-m-d H:i a'), 'text'));
        mysql_query($query_update1, $tams) or die(mysql_error());
        if(mysql_insert_id() > 0){
            echo "Suceees";
        }
        break;
    
    case 'get_reviews':
        $query_update1 = sprintf("SELECT * "
                                . "FROM market_review mv "
                                . "JOIN market_user ms ON mv.revhead = ms.user_id "
                                . "WHERE post_id = %s", 
                                GetSQLValueString($_GET['id'], 'int'));
        $reviewRS = mysql_query($query_update1, $tams) or die(mysql_error());
        $review_row = mysql_fetch_assoc($reviewRS);
        
        $rev = array();
        do {
            array_push($rev, $review_row);
        } while ($review_row = mysql_fetch_assoc($reviewRS));
        header("Content-Type: application/json");
        echo json_encode($rev);
        break;
        
    case'get_post':
       $postSQL = sprintf("SELECT * "
                . "FROM market_post mp "
                . "RIGHT JOIN market_post_img mpi "
                . "ON mp.postid = mpi.postid WHERE mp.status = 'active' "
                . "GROUP BY mp.postid %s %s ", $limit1, $offset);
        $postRS = mysql_query($postSQL, $tams) or die(mysql_error());
        $post_row = mysql_fetch_assoc($postRS);
        $post_num_row = mysql_num_rows($postRS);
        
        $data = array();
        if($post_num_row > 0){
           do{
              $data[] = $post_row;
           }while($post_row = mysql_fetch_assoc($postRS));
           header("Content-Type: application/json");
           echo json_encode($data);
        }
        
        
        break;
    default :
        break;
        
       
}