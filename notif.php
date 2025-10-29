<?php
session_start();
include 'config.php';

$response = ["count"=>0, "html"=>"Belum ada notifikasi."];

if(isset($_SESSION['user_id'])){
    $uid = $_SESSION['user_id'];
    $q = $conn->query("
    SELECT o.id, o.status, o.created_at
    FROM orders o
    WHERE o.user_id = $uid 
      AND (o.notif_read = 0 OR o.status != 'selesai')
    ORDER BY o.created_at DESC
    LIMIT 10
");




    if($q->num_rows > 0){
        $html = "";
        while($r = $q->fetch_assoc()){
            $status = ucfirst($r['status']); 
            $html .= "<div class='px-3 py-2 border-b text-sm'>
                        <a href='orders_detail.php?order_id={$r['id']}' class='block'>
                            Pesanan <span class='font-semibold'>#{$r['id']}</span> 
                            <span class='text-blue-600 font-medium'>$status</span><br>
                            <span class='text-xs text-gray-400'>" . date("d M H:i", strtotime($r['created_at'])) . "</span>
                        </a>
                      </div>";
        }
        $response = ["count"=>$q->num_rows, "html"=>$html];
    }
}
echo json_encode($response);
