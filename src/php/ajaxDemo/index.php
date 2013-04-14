<?php
session_start();
$_SESSION["running"] = time();
$_SESSION["inbound"] = array();
$_SESSION["outbound"] = array();

$target = "***********";//conversation target number/JID

?>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript">
    $(document).ready(function()
    {
        $("#message").keypress(function(e) {
            if(e.which == 13)
            {
                sendMessage();
            }
        });
        Listen();
        getMessages();
    });
    
    function Listen()
    {
        $.ajax({
            url: "socket.php",
            cache: false,
            dataType: "html",
            timeout: 1000,//don't wait for it to finish
            method: "POST",
            });
        setTimeout(function() {Listen()}, 60000);//page max execution time
    }
    
    function getMessages()
    {
        $.ajax({
            url: "ajax.php",
            cache: false,
            dataType: "json",
            method: "POST",
            data: {
                    method: "pollMessages",
                }}).done(function(data) {
                    if(data)
                    {
                        for(var i in data.messages)
                        {
                            addMessage(data.messages[i], "toMe");
                        }
                    }
                    setTimeout(function() {getMessages()}, 1000);
        });
    }
    
    function addMessage(message, cssclass)
    {
        $("#conversation").append($("<div></div>").addClass("message").addClass(cssclass).text(message));
    }
    
    function sendMessage()
    {
        var message = $("#message").val();
        if(message != '')
        {
            addMessage(message, "fromMe");
            $("#message").val("");
            $.ajax({
                url: "ajax.php",
                cache: false,
                dataType: "html",
                method: "POST",
                data: {
                        method: "sendMessage",
                        target: "<?php echo $target;?>",
                        message: message
                    }});
        }
    }
</script>
<style type="text/css">
    #conversation
    {
        width: 100px;
        background-color: #bbbbbb;
    }
    .message
    {
        border: 1px solid black;
        padding: 10px 10px 10px 10px;
        margin: 5px 5px 5px 5px;
        font-weight: bold;
        clear: both;
        display: inline-block;
    }
    .fromMe
    {
        background-color: #aaffaa;
        float: right;
        margin-left: 40px;
    }
    .toMe
    {
        background-color: #eeeeee;
        float: left;
        margin-right: 40px;
    }
</style>
<table border=1>
    <tr>
        <td id="conversation"></td>
    </tr>
    <tr>
        <td>
            <input type="text" id="message" />
            <button id="sendbutton" onclick="sendMessage()">Send</button>
        </td>
    </tr>
</table>