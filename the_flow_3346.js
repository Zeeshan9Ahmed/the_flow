const express = require('express');
const app = express();
var fs = require('fs');
const options = {
    key: fs.readFileSync('/home/serverappsstagin/ssl/keys/c2a88_d6811_bbf1ed8bd69b57e3fcff0d319a045afc.key'),
    cert: fs.readFileSync('/home/serverappsstagin/ssl/certs/server_appsstaging_com_c2a88_d6811_1665532799_3003642ca1474f02c7d597d2e7a0cf9b.crt'),
};
const server = require('https').createServer(options, app);
var io = require('socket.io')(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST","PATCH","DELETE"],
        credentials: true,
        transports: ['websocket', 'polling'],
        allowEIO3: false
    },
});
var mysql = require("mysql");
var con_mysql = mysql.createPool({
    host: "localhost",
    user: "serverappsstagin_user_the_flow",
    password: "I]2.AI0{0BpR",
    database: "serverappsstagin_the_flow",
    debug: true,
    charset:'utf8mb4'
});

var FCM = require('fcm-node');
var serverKey = 'AAAAJen85_U:APA91bFgzqk24VwgacZ1OZ2h06NGNMw6jL6IctkhnsjIi8rS0AiNn149wqrWNFEBxQtcjH5MUNlGkYpuxAX9mwHs7LU9uB4RWv_W7Cv-UnTobZB_7WTyZFofQx4XvEKOMH74hQ479eLk'; //put your server key here
var fcm = new FCM(serverKey);

// SOCKET START
io.on('connection', function(socket) {

    console.log('user connected. ', socket);

    // GET MESSAGES EMIT
    socket.on('get_messages', function(object) {
        console.log("GET_MESG", object.sender_id)
        var user_room = "user_" + object.sender_id;
        socket.join(user_room);

        get_messages(object, function(response) {
            if (response) {
                console.log("get_messages has been successfully executed...");
                io.to(user_room).emit('response', { object_type: "get_messages", data: response });
            } else {
                console.log("get_messages has been failed...");
                io.to(user_room).emit('error', { object_type: "get_messages", message: "There is some problem in get_messages..." });
            }
        });
    });

    //GET GROUP MESSAGE
    socket.on('group_get_messages', function(object) {
        var group_room = "group_" + object.group_id;
        var sender = "user_" + object.sender_id;
        socket.join(group_room);
        socket.join(sender);
        group_get_messages(object, function(response) {
            if (response) {
                console.log("get_messages has been successfully executed...");
                io.to(sender).emit('response', { object_type: "get_messages", data: response });
            } else {
                console.log("get_messages has been failed...");
                io.to(group_room).emit('error', { object_type: "get_messages", message: "There is some problem in get_messages..." });
            }
        });
    });

    // SEND MESSAGE EMIT
    socket.on('send_message', function(object) {
        var sender_room = "user_" + object.sender_id;
        var receiver_room = "user_" + object.reciever_id;
        console.log("trting to send mesg", object);
        send_message(object, function(response) {
            if (response) {
                
                if(response[0]['user_device_token'] == null){
                    io.to(sender_room).to(receiver_room).emit('response', { object_type: "get_message", data: response[0] });
                    console.log("Successfully sent with response: ");
                }else{
                    var message = { //this may vary according to the message type (single recipient, multicast, topic, et cetera)
                        to: response[0]['user_device_token'], 
                        collapse_key: 'your_collapse_key',
                        
                        notification: {
                            title:'Chat Notification',
                            body:response[0]['full_name']+' Send you a message',
                           // user_name: response[0]['full_name'],
                            notification_type:'chat',
                            redirection_id:object.sender_id,
                            vibrate:1,
                            sound:1
                        },
                        
                        data: {  //you can send only notification or only data(or include both)
                            title:'Chat Notification',
                            body:response[0]['full_name']+' Send you a message',
                            //user_name: response[0]['user_name'],
                            notification_type:'CHAT',
                            redirection_id:object.sender_id,
                            vibrate:1,
                            sound:1
                        }
                    };
                
                    fcm.send(message, function(err, response_two){
                        if (err) {
                            console.log("Something has gone wrong!");
                            io.to(sender_room).to(receiver_room).emit('response', { object_type: "get_message", data: response[0] });
                        } else {
                            // console.log("send_message has been successfully executed...");
                            io.to(sender_room).to(receiver_room).emit('response', { object_type: "get_message", data: response[0] });
                            console.log("Successfully sent with response: ", response_two);
                        }
                    });
                }
                
            } else {
                console.log("send_message has been failed...");
                io.to(sender_room).to(receiver_room).emit('error', { object_type: "get_message", message: "There is some problem in get_message..." });
            }
        });
    });

    //SEND GROUP MESSAGE
    socket.on('group_send_message', function(object) {
        var group_room = "group_" + object.group_id;
        socket.join(group_room);
        group_send_message(object, function(response) {
            if (response) {
                console.log("send_message has been successfully executed...");
                io.to(group_room).emit('response', { object_type: "get_message", data: response });
            } else {
                console.log("send_message has been failed...");
                io.to(group_room).emit('error', { object_type: "get_message", message: "There is some problem in get_messages..." });
            }
        });
    });
    // DELETE MESSAGE EMIT
    socket.on('delete_message', function(object) {
        var chat_id = object.chat_id;
        var sender_room = "user_" + object.sender_id;
        var receiver_room = "user_" + object.reciever_id;
        delete_message(object, function(response) {
            io.to(sender_room).to(receiver_room).emit('response', { object_type: "delete_message", data: chat_id });
        });
    });

    socket.on('disconnect', function() {
        console.log("Use disconnection", socket.id)
    });
});
// SOCKET END

// GET MESSAGES FUNCTION
var get_messages = function(object, callback) {
    con_mysql.getConnection(function(error, connection) {
        if (error) {
            callback(false);
        } else {
            connection.query(`select 
            users.full_name,
            users.avatar, 
            st_chat.chat_id, 
            st_chat.chat_sender_id,
            st_chat.chat_reciever_id, 
            st_chat.chat_group_id,
            st_chat.chat_message,
            st_chat.chat_type,
            st_chat.created_at
            from st_chat 
            inner join users on st_chat.chat_sender_id = users.id
            WHERE (st_chat.chat_sender_id = ${object.sender_id} 
            AND st_chat.chat_reciever_id=${object.reciever_id}) 
            OR (st_chat.chat_sender_id=${object.reciever_id} 
            AND st_chat.chat_reciever_id=${object.sender_id}) 
            order by st_chat.chat_id ASC`, function(error, data) {
                connection.release();
                if (error) {
                    callback(false);
                } else {
                    callback(data);
                }
            });
        }
    });
};

//GROUP MESSAGE
var group_get_messages = function(object, callback) {
    con_mysql.getConnection(function(error, connection) {
        if (error) {
            callback(false);
        } else {
            connection.query(`select 
            st_user.user_name,
            st_user.user_image, 
            st_chat.chat_id, 
            st_chat.chat_sender_id,
            st_chat.chat_reciever_id, 
            st_chat.chat_group_id,
            st_chat.chat_message,
            st_chat.chat_type,
            st_chat.created_at
            from st_chat 
            inner join st_user on st_chat.chat_sender_id = st_user.user_id
            WHERE st_chat.chat_group_id=${object.group_id} order by st_chat.chat_id ASC`, function(error, data) {
                connection.release();
                if (error) {
                    callback(false);
                } else {
                    callback(data);
                }
            });
        }
    });
};

// SEND MESSAGE FUNCTION
var send_message = function(object, callback) {
    console.log("Send msf call bacj")
    con_mysql.getConnection(function(error, connection) {
        if (error) {
            console.log("CONNECTIOn ERROR ON SEND MESSAFE")
            callback(false);
        } else {
            var new_message = mysql_real_escape_string (object.message);
            connection.query(`INSERT INTO st_chat (chat_sender_id , chat_reciever_id , chat_message, chat_type,created_at) VALUES ('${object.sender_id}' , '${object.reciever_id}', '${new_message}', '${object.chat_type}',NOW())`, function(error, data) {
                if (error) {
                    console.log("FAILED TO VERIFY LIST")
                    callback(false);
                } else {
                    console.log("update_list has been successfully executed...");
                    connection.query(`SELECT 
                        u.full_name,
                        u.avatar, 
                        (select device_token from users where id = '${object.reciever_id}') as user_device_token,
                        c.*
                        FROM users AS u
                        JOIN st_chat AS c
                        ON u.id = c.chat_sender_id
                        WHERE c.chat_id = '${data.insertId}'`, function(error, data) {
                        connection.release();
                        if (error) {
                            callback(false);
                        } else {
                            callback(data);
                        }
                    });
                }
            });
            
        }
    });
};

//SEND GROUP MESSAGE
var group_send_message = function(object, callback) {
    console.log("Send msf call bacj")
    con_mysql.getConnection(function(error, connection) {
        if (error) {
            console.log("CONNECTIOn ERROR ON SEND MESSAFE")
            callback(false);
        } else {
            var new_message = mysql_real_escape_string (object.message);
            connection.query(`  INSERT INTO st_chat (chat_sender_id , chat_group_id , chat_message,created_at) VALUES ('${object.sender_id}' , '${object.group_id}', '${new_message}',NOW())`, function(error, data) {
                if (error) {
                    console.log("FAILED TO VERIFY LIST")
                    callback(false);
                } else {
                    console.log("update_list has been successfully executed...");
                    connection.query(`SELECT 
                        u.user_name,
                        u.user_image, 
                        c.*
                        FROM st_user AS u
                        JOIN st_chat AS c
                        ON u.user_id = c.chat_sender_id
                        WHERE c.chat_id = '${data.insertId}'`, function(error, data) {
                        connection.release();
                        if (error) {
                            callback(false);
                        } else {
                            callback(data);
                        }
                    });

                }
            });
        }
    });
};
// DELETE MESSAGE FUNCTION
var delete_message = function(object, callback) {
    con_mysql.getConnection(function(error, connection) {
        if (error) {
            console.log("CONNECTIOn ERROR ON SEND MESSAFE")
            callback(false);
        } else {
            connection.query(`delete from st_chat where chat_id = '${object.chat_id}'`, function(error, data) {
                if (error) {
                    console.log("FAILED TO VERIFY LIST")
                    callback(false);
                } else {
                    callback(true);
                }
            });
        }
    });
};

function mysql_real_escape_string (str) {
    return str.replace(/[\0\x08\x09\x1a\n\r"'\\\%]/g, function (char) {
        switch (char) {
            case "\0":
                return "\\0";
            case "\x08":
                return "\\b";
            case "\x09":
                return "\\t";
            case "\x1a":
                return "\\z";
            case "\n":
                return "\\n";
            case "\r":
                return "\\r";
            case "\"":
            case "'":
            case "\\":
            case "%":
                return "\\"+char; // prepends a backslash to backslash, percent,
                                  // and double/single quotes
            default:
                return char;
        }
    });
}


// SERVER LISTENER
server.listen(3346, function() {
    console.log("Server is running on port 3346");
});