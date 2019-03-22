<?php session_start();?>
<!DOCTYPE html>
<html>
	<head>
		<title></title>
	</head>
	<body>
<?php
if(isset($_POST["email"]) && isset($_POST["pass"]))
{
	$email = $_POST["email"];
	$pass = $_POST["pass"];
	/* cURL login */
	$curl = curl_init();
	$params = array(
        'email' => $email,
        'password' => $pass
	);
	curl_setopt_array($curl, array(
		CURLOPT_URL => "http://api.loc/api/login",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $params,
	));
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	/* end cURL login */
	if ($err) {
		echo "cURL Error #:" . $err;
	} else {
		$result_login = json_decode($response);
		if(isset($result_login->success) && $result_login->success == true) {
		    $_SESSION['token'] = $result_login->data->token;
        } else {
        	echo "Incorect Login";
        }
	}
}

?>

<?php
if(isset($_SESSION['token']) && !empty($_SESSION['token'])):
	$token = $_SESSION['token'];

    /* show single product */
    if(isset($_POST['dataForm'])) {
        $id = $_POST['dataForm'];

	    $curl = curl_init();
	    curl_setopt_array($curl, array(
		    CURLOPT_URL => "http://api.loc/api/products/$id",
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_ENCODING => "",
		    CURLOPT_MAXREDIRS => 10,
		    CURLOPT_TIMEOUT => 30,
		    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		    CURLOPT_CUSTOMREQUEST => "GET",
		    CURLOPT_HTTPHEADER => array(
			    "Accept: application/json",
			    "Authorization: Bearer $token",
		    ),
	    ));
	    $response = curl_exec($curl);
	    $err = curl_error($curl);
	    curl_close($curl);
	    if ($err) {
		    echo "cURL Error #:" . $err;
	    } else {
		    $result_product = json_decode($response);
		    if($result_product->success == true) {
		        $data = $result_product->data;
			    echo "<h2>$result_product->message</h2>";
			    echo "<p>$data->detail</p>";
                die;
		    }
	    }
    }

	/* create product */
	if( isset($_POST["name"]) && isset($_POST["detail"]) ) {
		$name = $_POST["name"];
		$detail = $_POST["detail"];
		/* cURL login */
		$curl = curl_init();
		$params = array(
			'name' => $name,
			'detail' => $detail
		);
		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://api.loc/api/products",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_HTTPHEADER => array(
				"Accept: application/json",
				"Authorization: Bearer $token",
			),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		/* end cURL login */
		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			$result_create = json_decode($response);
			if($result_create->success == true) {
				echo "<h2>$result_create->message</h2>";
			}
		}
	}
	/* end create */

    /* show all products */
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => "http://api.loc/api/products",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Authorization: Bearer $token",
		),
	));
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err):
		echo "cURL Error #:" . $err;
	else:
		$result_details = json_decode($response);?>
        <form action="" method="post">
            <p><input type="text" name="name"></p>
            <p><textarea name="detail"></textarea></p>
            <p><input type="submit" name="create_product" value="create product"></p>
        </form>
        <?php

        if(isset($result_details->success)):?>
            <table style="border:1px solid #ccc;">
                <thead>
                <th>#</th>
                <th>Name</th>
                <th>Detail</th>
                <th>Created</th>
                </thead>
                <tbody>
                <?php
                if(isset($result_details->data)):
                foreach($result_details->data as $product) :?>
                    <tr>
                        <td><?=$product->id?></td>
                        <td><a href="single-product.php?id=<?=$product->id?>" class="get" data-id="<?=$product->id?>"><?=$product->name?></a></td>
                        <td><?=$product->detail?></td>
                        <td><?=$product->created_at?></td>
                    </tr>
                <?php endforeach;
                endif;?>
                </tbody>
            </table>
            <div id="single-product"></div>
        <?php endif;?>

		<p><a href="logout.php">Logout</p>
	<?php
	endif;

else:?>
    <p><a href="register.php">Registration</a></p>
    <form action="" method="POST">
        <p>Email: <input type="email" name="email"></p>
        <p>Password: <input type="password" name="pass"></p>
        <input type="submit" name="login" value="Login">
    </form>
	<?php endif;?>
<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>
<script>
$(document).ready(function()
{
    /*$(document).on("click",".get",function(e)
    {
        e.preventDefault();
        var id = $(this).attr('data-id');

        $.ajax({
            url: "index.php",
            method: 'POST',
            data: {
                dataForm: id
            },
            success: function(data) {
                $("#single-product").html(data);
            }
        });
        return false;
    });*/
});
</script>
	</body>
</html>