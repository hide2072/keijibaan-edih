    <!DOCTYPE html>
<html lang=ja>
<head>
        <meta charset="utf-8" />
    </head>
    <body>
        
    <?php
    // DB接続設定
    $dsn = 'データベース名';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
    // DB作成
    $sql = "CREATE TABLE IF NOT EXISTS tbkeijiban"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "com TEXT,"
    . "time TIMESTAMP,"
    . "pass TEXT"
    .");";
    $stmt = $pdo->query($sql);
            
         
        // データ編集or新規投稿
        if (isset($_POST['submit']) &&
            !empty($_POST['name']) && 
            !empty($_POST['com']) && 
            !empty($_POST['pass'])){
            //入力データ（名前とコメント）の受け取りを変数に代入
            $name=$_POST['name'];
            $com =$_POST['com'];
            $pass=$_POST['pass'];
            // editNoがある場合は編集、ある場合は編集ないときは新規投稿 ***ここで判断
            // データ編集
            if (!empty($_POST['editNo'])) {
                $id = $_POST['editNo'];
                $sql = 'UPDATE tbkeijiban SET name=:name,com=:com,pass=:pass WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':com', $com, PDO::PARAM_STR);
                $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            } else { // 新規投稿
                $sql = $pdo -> prepare("INSERT INTO tbkeijiban (name, com, pass) VALUES (:name, :com, :pass)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':com', $com, PDO::PARAM_STR);
                $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
                $sql -> execute();
            }
            header("Location: " . $_SERVER["SCRIPT_NAME"]);
        exit;
        }
        
        //削除機能
        //削除フォームの送信の有無で処理を分岐
        if(!empty($_POST['del'])) {
            $id = $_POST['delNo'];
            $delPass = $_POST['delPass'];
            // パスワード取得
            $sql = 'select * from tbkeijiban where id=:id';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll();
            foreach ($results as $row) {
                $password_check = $row['pass'];
            }
            
            if ($password_check == $delPass) {
                $sql = 'delete from tbkeijiban where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
         
         //以下からスタートさせる
         
        //編集選択機能
        //編集フォームの送信の有無で処理を分岐
        if(isset($_POST["edi"]) && !empty($_POST["edi"])){
        //入力データの受け取りを変数に代入
            $editnumber = $_POST['edi'];
            $ediPass = $_POST['ediPass'];
            $sql = 'select * from tbkeijiban where id=:id';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $editnumber, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll();
            foreach ($results as $row) {
                $password_check = $row['pass'];
            }
            if ($ediPass == $password_check) {
                foreach ($results as $row) {
                    $editname = $row['name'];
                    $editcomment = $row['com'];
                }
            } else {
                $editnumber = ""; // パスワードが違ったらeditNoを空にする
            }
        }
        ?>
        <h1>簡易掲示板</h1><br>
    <br><form action="" method="post">
      <input type="text" name="name" placeholder="名前" value="<?php if(isset($editname)) {echo $editname;} ?>"><br>
      <input type="text" name="com" placeholder="コメント" value="<?php if(isset($editcomment)) {echo $editcomment;} ?>"><br>
      <input type="hidden" name="editNo" value="<?php if(isset($editnumber)) {echo $editnumber;} ?>">
      <input type="text" name="pass" placeholder="パスワード">
      <input type="submit" name="submit" value="送信">
    </form>
<br>
    <form action="" method="post">
      <input type="text" name="delNo" placeholder="削除対象番号(半角文字)"><br>
      <input type="text" name="delPass" placeholder="パスワード">
      <input type="submit" name="del" value="削除">
    </form>
<br>
    <form action="" method="post">
      <input type="text" name="edi" placeholder="編集対象番号(半角文字)"><br>
      <input type="text" name="ediPass" placeholder="パスワード">
      <input type="submit" value="編集">
    </form>
    <br>
    ＊編集時、必要に応じてパスワードを変えられます<br>
    <br>
        <?php
        // データ表示
        $sql = 'SELECT * FROM tbkeijiban';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row) {
            // 出力
            echo "番号：".$row['id']."<br>";
            echo "名前：".$row['name']."<br>";
            echo "コメント：".$row['com']."<br>";
            echo '<hr>';
        }
        ?>
    </body>
    </html>