<?php 

$post = false;
$login      = null;
$name       = null;
$password   = null;
$realm      = null;
$hash       = null;

if(isset($_POST) && $_POST)
{
    $post = true;

    $name       = $_POST['inputName'];
    $login      = $_POST['inputLogin'];
    $password   = $_POST['inputPassword'];
    $realm      = $_POST['inputRealm'];
    $length     = $_POST['inputLength'];

    if( $password &&
        $realm)
    {
        include "pbkdf2.php";
        $rounds = 1000;

        $encode = implode(':', array($name, $login, $password, $realm));
        $salt = 'abc';

        $hash = base64_encode(pbkdf2('sha512', $encode, $salt, $rounds, $length*8, true));
    }

    if(strtolower(isset($_SERVER['HTTP_X_REQUESTED_WITH'])? $_SERVER['HTTP_X_REQUESTED_WITH'] : '') == 'xmlhttprequest')
    {
        header("Cache-Control: no-store, no-cache, must-revalidate");
        echo json_encode(array('status' => isset($hash), 'hash' => isset($hash) ? $hash : null));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Hash Generator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.css" rel="stylesheet" media="screen">
  </head>
  <body>
    <div class="container">
        <h1>Hash My Password</h1>
        <form class="form-horizontal" id="mainForm" method="post">
            <fieldset>
                <legend></legend>
  <div class="control-group">
    <label class="control-label" for="inputLogin">Login</label>
    <div class="controls">
      <input type="text" id="inputLogin" name="inputLogin" placeholder="Login" value="<?php echo htmlentities($login); ?>" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="inputName">Name</label>
    <div class="controls">
      <input type="text" id="inputName" name="inputName" placeholder="Name" value="<?php echo htmlentities($name); ?>" />
    </div>
  </div>
  <div class="control-group <?php if($post && !$password) echo 'error' ?>">
    <label class="control-label" for="inputPassword">Password</label>
    <div class="controls">
      <input type="password" id="inputPassword" name="inputPassword" placeholder="Password" value="<?php //echo htmlentities($password);?>" />
    </div>
  </div>
  <div class="control-group <?php if($post && !$realm) echo 'error' ?>">
    <label class="control-label" for="inputRealm">Website or App</label>
    <div class="controls">
      <input type="text" id="inputRealm" name="inputRealm" placeholder="facebook.com" value="<?php echo htmlentities($realm); ?>" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="inputLength">Length</label>
    <div class="controls">
      <?php foreach(range(1, 4) as $length): ?>
        <label class="radio inline">
            <input type="radio" name="inputLength" value="<?php echo $length; ?>" <?php
                if($length == 1): ?>
                    checked="checked"
                <?php endif; ?>>
            <?php echo $length; ?>
        </label>
      <?php endforeach ?>
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
            <button type="submit" id="btnSubmit" class="btn">Generate</button>
    </div>
  </div>
            </fieldset>
            <div id="resultPanel">
                <?php //if($hash): ?>
                <fieldset>
                <legend>Your Hash for <span id="txtRealm"><?php echo $realm; ?></span></legend>
                <input type="text" id="hash" name="hash" class="input-xxlarge" value="<?php echo htmlentities($hash); ?>" />
            </fieldset>
                <?php //endif ?>
            </div>
        </form>
    </div>
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.js"></script>
    <script type="text/javascript">

    function Input (elem)
    {
        this.elem = elem;
    }

    Input.prototype.setErrorClass = function() {
        this.setErrorClassValue('add');
    }
    
    Input.prototype.unsetErrorClass = function() {
        this.setErrorClassValue('remove');
    }
    
    Input.prototype.setErrorClassValue = function(op) {
        $div = this.elem.parent().parent('.control-group');
        if( op == 'add' ) {
            $div.addClass('error');
        } else if ( op == 'remove' ) {
            $div.removeClass('error');
        }
    }

    Input.prototype.isEmpty = function() {
        return this.elem.val() == '';
    }

    $('#resultPanel').hide();
    $('#mainForm').on('submit', function(e){
        $('#resultPanel').hide();

        var $name = $('#inputName');
        var $login = $('#inputLogin');
        var $realm = $('#inputRealm');
        var $password = $('#inputPassword');
        var $length = $('input[name=inputLength]:checked');
        
        var inputName = new Input($name);
        var inputLogin = new Input($login);
        var inputRealm = new Input($realm);
        var inputPassword = new Input($password);
        var inputLength = new Input($length);
        
        inputName.unsetErrorClass();
        inputLogin.unsetErrorClass();
        inputRealm.unsetErrorClass();
        inputPassword.unsetErrorClass();
        inputLength.unsetErrorClass();

        if( !inputRealm.isEmpty() && 
            !inputPassword.isEmpty() ) {
            $.ajax({
                //url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                dataType: 'json',
                cache: false,
                type: 'POST',
                data: {
                    inputName: $name.val(),
                    inputLogin: $login.val(),
                    inputRealm: $realm.val(),
                    inputPassword: $password.val(),
                    inputLength: $length.val()
                }
            }).done(function(data){
                if(data.status)
                {
                    var $hash = $('#hash');
                    $hash.val(data.hash);
                    $('#txtRealm').text($realm.val());
                    $('#resultPanel').show();
                    $hash.select();
                } else {
                    console.log(data);
                }
            });
        } else {
            if( inputRealm.isEmpty() ) {
                inputRealm.setErrorClass('add');
            }
            if( inputPassword.isEmpty() ) {
                inputPassword.setErrorClass('add');
            }
        }

        return false;
    });
    </script>
  </body>
</html>
