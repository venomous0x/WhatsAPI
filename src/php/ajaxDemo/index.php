<?php
session_start();
$_SESSION["running"] = time();
$_SESSION["inbound"] = array();
$_SESSION["outbound"] = array();

$target = "***********"; //conversation target number/JID
?>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript">
    var target = "<?php echo $target; ?>";
    $(document).ready(function()
    {
        $("#message").keypress(function(e) {
            if (e.which == 13)
            {
                sendMessage();
            }
        });
        Listen(true);
        getMessages();
    });

    function Listen(initial)
    {
        $.ajax({
            url: "socket.php",
            cache: false,
            dataType: "html",
            timeout: 70000,
            method: "POST",
            data: {
                initial: initial,
                target: target
            }
        }).done(function(data) { // on success, you can get the parameter as "data".
            //write debug info
            //if(data)
            //{
            //    var foo = $("#debug").text();
            //    $("#debug").text(foo + data);
            //}		    
        }).always(function() { //if DONE is used, the service is stopped in case of failure of connection.
            setTimeout(function() {
                Listen(false)
            }, 1000);
        });

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
            }
        }).done(function(data) {
            if (data)
            {
                if (data.profilepic != "")
                {
                    $("#profilepic").attr("src", data.profilepic);
                }
                for (var i in data.messages)
                {
                    addMessage(data.messages[i], "toMe");
                }
            }
        }).always(function() { //if DONE is used, the service is stopped in case of failure of connection.
            setTimeout(function() {
                getMessages()
            }, 1000);
        });
    }

    function addMessage(message, cssclass)
    {
        $("#conversation").append($("<div></div>").addClass("message").addClass(cssclass).html(message));
    }

    function sendMessage()
    {
        var message = $("#message").val();
        if (message != '')
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
                    target: target,
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
        <td width=96 height=96>
            <img width=96 height=96 id="profilepic" />
        </td>
        <td id="header">
            <span id="contactname"></span>
            <br />
            <span id="contactstatus"></span>
        </td>
    </tr>
    <tr>
        <td colspan=2 id="conversation"></td>
    </tr>
    <tr>
        <td colspan=2 >
            <input type="text" id="message" />
            <button id="sendbutton" onclick="sendMessage()">Send</button>
        </td>
    </tr>
    <tr>
        <td colspan=2 id="debug" style="white-space: pre"></td>
    </tr>
</table>