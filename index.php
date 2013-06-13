<?php 
    session_start();

    $url = "http://videtwo.com/recorder";
    
    //print_r($_SESSION);
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>Isolated spoken digit recognition</title>
	<!--link rel="stylesheet" href="style.css" /-->
    <style>
        body {
            font: 14px/1.5 helvetica-neue, helvetica, arial, san-serif;
            /*padding:10px;*/
        }

        h1, h2, h3, h4  {
            margin-top:0;
        }

        #user {
            width: 300px;
            margin: 0 auto;
            margin-bottom: 5px;
            background: #ececec;
            padding: 5px 20px;
            border: 1px solid #ccc;
<?php if(!isset($_SESSION['user'])) print "display: none;"; ?>
        }

        #main {
            width: 300px;
            margin:auto;
            background: #ececec;
            padding: 20px;
            border: 1px solid #ccc;
        }

        #counter {
            font-weight: bold;
            color: green;
        }
        
        #image-list {
            list-style:none;
            margin:0;
            padding:0;
        }
        #image-list li {
            background: #fff;
            border: 1px solid #ccc;
            text-align:center;
            padding:20px;
            margin-bottom:19px;
        }
        #image-list li img {
            width: 258px;
            vertical-align: middle;
            border:1px solid #474747;
        } 
        #response td {
            text-align: center;
        }
    </style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
    <script src="recorder.js" type="text/javascript"></script>
    <script>    
        
        function callback(stream) {
           var context = new webkitAudioContext();
           var mediaStreamSource = context.createMediaStreamSource(stream);
           rec = new Recorder(mediaStreamSource);          
        }

        var interval;
        var count = 0;
        var response;
        
        var record_length = 2; //in second
        var message = "Click on the Record button and then say a number between 0 and 9<br />You have <span id='counter'>" + record_length + "</span> second to record your voice.";
                 
        function counter() {
            count++;
            $("#counter").text(record_length - count);
            if(count==record_length) {
                count = 0;
                clearInterval(interval);
                upload();
            }
        }
                
        function upload() {
            
            rec.stop();
            
            $('#message').text("Please wait...");
                            
            rec.exportWAV(function(blob) {
                
                rec.clear(); 
                
                function upload(blobOrFile) {
                    var xhr = new XMLHttpRequest();
                    
                    xhr.open('POST', 'recorder.php', true);
                    
                    xhr.onload = function(e) {
                        if (this.status == 200) {

                            response = JSON.parse(this.response);
                                                        
                            src = 'php-waveform-png.php?wav=' + response.file + '.wav';
                            
                            $('#response > tbody > tr:first').after('<tr><td><img width="100" height="100" src="' + src + '" /></td><td class="numba"></td></tr>');
                            
                            $('#message').text("Did you say: " + response.guess + " ?");
                            $('#hidden').show();                                                   
                        }
                    };

                    // Listen to the upload progress.
                    /*
                    var progressBar = document.querySelector('progress');

                    xhr.upload.onprogress = function(e) {
                        if (e.lengthComputable) {
                            progressBar.value = (e.loaded / e.total) * 100;
                            progressBar.textContent = progressBar.value; // Fallback for unsupported browsers.
                        }
                    };
                    */

                    xhr.send(blobOrFile);
                }

                //upload(new Blob(['hello world'], {type: 'text/plain'}));
                upload(blob);
                
                //var player = document.getElementById('player');
                //player.innerHTML = ("<audio id='player' src='" + URL.createObjectURL(blob) + "' controls />");
            });             
        }
        
        $(document).ready(function() {

            $("#message").html(message);

            navigator.webkitGetUserMedia({audio:true}, callback);
                                        
            formdata = new FormData();
            
            $('#upload').click(function() {
                upload();
            }); 
                
            $('#record').click(function() {
                $('#record').hide();
                
                try {
                    rec.record();
                } catch (e) {
                    alert('Please reload the page and allow the browser to use your microphone first!');
                }
                
                interval = setInterval(function(){counter()},1000);       
            });

            $('#yes').click(function() {
                
                $('#hidden').hide();   
                $('#message').text("Saving...");
                
                $(".numba")[0].innerHTML = response.guess;
                
                $.ajax({
                    type: "POST",
                    url: "save.php",
                    data: {filename: response.file, number: response.guess}
                }).done(function( msg ) {
                    $('#message').text("I'm so smart!");
                    $('#continue').show();                    
                });   
            });

            $('#no').click(function() {   
                $('#hidden').hide();
                $('#message').text('What number did you say?');
                $('#correct').show();
                
            });

            $('#cancel').click(function() {  
                $('#hidden').hide(); 
                $('#message').text('Cancelled');
                $(".numba")[0].innerHTML = 'C';
                $('#continue').show(); 
            });

            $('#teach').click(function() {
                
                var numba = parseInt($('input[id=numba]').val());
                
                if(isNaN(numba) || numba < 0 || numba > 9) {
                    alert('Please enter a number between 0 and 9 !');
                    $('input[id=numba]').val('');
                    $('input[id=numba]').focus();
                    return false;
                }
                               
                $('#correct').hide();
                $('#message').text('Please wait...');   
                
                $(".numba")[0].innerHTML = numba;
                
                $.ajax({
                    type: "POST",
                    url: "teach.php",
                    data: {filename: response.file, number: numba}
                }).done(function( msg ) {
                    $('#message').text("Saving...");
                    $.ajax({
                        type: "POST",
                        url: "save.php",
                        data: {filename: response.file, number: numba}
                    }).done(function( msg ) {
                        $('#message').text("Lesson learned");
                        $('#continue').show();                    
                    });                  
                });
            });
            
            $('#continuebtn').click(function() {
                $('#continue').hide();

<?php if(! isset($_SESSION['user'])) { ?>  
                if($('#user').is(':hidden')) {             
                    $('#user > a').attr('href','<?php print $url; ?>' + '/setuser.php?user=' + response.user); 
                    $('#user').show(); 
                }
<?php } ?>                                            
                $('#message').html(message);                  
                $('#record').show();         
            });

            /*
            $('#saveas').click(function() {
                saveAs(blob, "blob.wav");         
            });
            */                                                 
        });
    </script>
</head>
<body>
    <div id="user">
<?php if(isset($_SESSION['user'])) { ?>
        Wellcome back! Your unique session id is:</br />
        <a href="setuser.php?user=<?php print $_SESSION['user']; ?>"><?php print $_SESSION['user']; ?></a>
<?php } else { ?>
        <a>Click here to save your session for next time</a>
<?php } ?>
    </div>
        
    <div id="main">
        
		<h4>Isolated spoken digit recognition</h4>
        
        <p id="message"></p>

        <p id="hidden" style="display:none">                
            <button id="yes">Yes</button>     
            <button id="no">No</button> 
            <button id="cancel">Cancel</button> 
        </p>  

        <p id="correct" style="display:none">                
            <input id="numba" type="text" maxlength="1" size="1" /> (0-9)
            <button id="teach">Send</button> 
        </p>  

        <p id="continue" style="display:none">                
            <button id="continuebtn">Continue</button> 
        </p>  
        
        <p><button id="record">Record</button></p>
        
        <table id="response" border="1" width="100%">
            <thead>
                <tr>
                    <th colspan="2">Learned examples</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Waveform</td>
                    <td>Number</td>
                </tr>
            </tbody>
        </table>

    </div>
</body>
</html>
