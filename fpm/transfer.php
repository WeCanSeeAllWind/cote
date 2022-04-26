<?php
  require "db.php";

  $start_time = time();
  echo "시작시간: ".$start_time."\n";

  //DB 연결
  $con = getDB();

  //시작과 끝 idx 받아오기
  $prev_idx = mysqli_fetch_assoc(mysqli_query($con, "SELECT last_idx FROM login_check WHERE id = 1;"))["last_idx"];
  $end_idx = mysqli_fetch_assoc(mysqli_query($con, "SELECT id FROM member ORDER BY id DESC LIMIT 1"))["id"];

  // 데이터 이동 함수 정의
  function transfer($start_idx, $end_idx, $con) {
    // 옮길 영역 탐색(1000개 이내)
    $last_idx = $start_idx;
    $sql_find_range = "SELECT * FROM member 
      WHERE id > $start_idx
      AND last_login_time <= DATE_ADD(NOW(), INTERVAL -4 MONTH)
      AND last_login_time >= DATE_ADD(join_date, INTERVAL 4 MONTH)
      LIMIT 1000;";
    $transfer_range = mysqli_query($con, $sql_find_range);

    // Target 데이터가 남은 member 테이블에 하나도 없다면 바로 함수 종료
    if ($transfer_range->num_rows == 0) {
      mysqli_query($con, "UPDATE login_check SET last_idx = $end_idx WHERE id = 1;");
      return false;
    };

    // 쿼리 준비
    $sql_trasfer = "INSERT INTO unconnected_member VALUE ";
    if ($transfer_range) {
      while ($row = mysqli_fetch_assoc($transfer_range)) {
        $last_idx = $row["id"];
        $sql_trasfer = $sql_trasfer."(\"{$row['id']}\", \"{$row['email']}\", \"{$row['name']}\", \"{$row['join_date']}\", \"{$row['last_login_time']}\"),";
      }
      if ($sql_trasfer != "INSERT INTO unconnected_member VALUE ") {
        $sql_trasfer = mb_substr($sql_trasfer, 0, -1).";";
      } else {
        $sql_trasfer = false;
      }
    } else {
      printf("Error message: %s\n", mysqli_error($con));
    }

    // login_check 테이블 수정
    if ($transfer_range->num_rows < 1000) {
      $sql_login_check = "UPDATE login_check SET last_idx = $end_idx WHERE id = 1;";
    } else {
      $sql_login_check = "UPDATE login_check SET last_idx = $last_idx WHERE id = 1;";
    }
    $res_login_check = mysqli_query($con, $sql_login_check);
    if (!$res_login_check) {
      printf("Error message: %s\n", mysqli_error($con));
    }

    // unconnected_member 테이블에 데이터 추가
    $res_transfer = mysqli_query($con, $sql_trasfer);
    if (!$res_transfer) {
      printf("Error message: %s\n", mysqli_error($con));
    }
    
    // member 테이블에서 데이터 삭제
    $sql_delete = "DELETE FROM member 
      WHERE id > $start_idx
      AND id <= $last_idx
      AND last_login_time <= DATE_ADD(NOW(), INTERVAL -4 MONTH)
      AND last_login_time >= DATE_ADD(join_date, INTERVAL 4 MONTH);";
    $res_delete = mysqli_query($con, $sql_delete);
    if (!$res_delete) {
      printf("Error message: %s\n", mysqli_error($con));
    }
  }

  // member 테이블을 탐색하며 데이터 이동
  while ($prev_idx < $end_idx) {
    transfer($prev_idx, $end_idx, $con);
    $prev_idx = mysqli_fetch_assoc(mysqli_query($con, "SELECT last_idx FROM login_check WHERE id = 1;"))["last_idx"];
  }

  // 데이터 이동이 완료된 후 login_check 테이블 수정
  $is_complete = mysqli_fetch_assoc(mysqli_query($con, "SELECT is_complete FROM login_check WHERE id = 1;"))["is_complete"];
  if (!$is_complete) {
    $sql_complete = "UPDATE login_check SET last_check_time = NOW(), is_complete = TRUE WHERE id = 1;";
    $result = mysqli_query($con, $sql_complete);
    if (!$result) {
      printf(mysqli_error($con));
    }
  }

  //DB 연결 해제
  mysqli_close($con);

  $end_time = time();
  echo "종료시간: ".$end_time."\n";

  $delay = $end_time - $start_time;
  echo "걸린시간: ".$delay."초\n";
?>
