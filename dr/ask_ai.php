<?php
// ================== CONFIG ==================
$OPENAI_API_KEY = "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";

// ============== AJAX REQUEST HANDLE =========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $userMessage = trim($_POST['message'] ?? '');

    if ($userMessage === '') {
        echo json_encode(['reply' => 'Please apni bemari ya symptoms likhein.']);
        exit;
    }

    $prompt = "Tum ek medical guidance assistant ho.
User ke symptoms ke base par sirf yeh batao ke
kis type ke doctor (specialist) ke paas jana chahiye.
Diagnosis, dawa ya treatment suggest na karo.
Har jawab ke end me yeh line add karo:
'Final diagnosis ke liye doctor se zaroor consult karein.'

User symptoms: $userMessage";

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ],
        "temperature" => 0.3
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $OPENAI_API_KEY"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    $reply = $result['choices'][0]['message']['content'] ?? 'Koi jawab nahi mila.';

    echo json_encode(['reply' => $reply]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Finder Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
        }
        .chat-box {
            height: 400px;
            overflow-y: auto;
            background: #ffffff;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }
        .user-msg {
            text-align: right;
            margin-bottom: 10px;
        }
        .bot-msg {
            text-align: left;
            margin-bottom: 10px;
        }
        .bubble {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 20px;
            max-width: 75%;
        }
        .user-bubble {
            background: #0d6efd;
            color: #fff;
        }
        .bot-bubble {
            background: #e9ecef;
            color: #000;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4>🩺 Doctor Finder Chatbot</h4>
                    <small>Apni bemari likhein aur janain kis doctor ke paas jana chahiye</small>
                </div>

                <div class="card-body">
                    <div class="chat-box mb-3" id="chatBox">
                        <div class="bot-msg">
                            <div class="bubble bot-bubble">
                                Assalam-o-Alaikum! Apni bemari ya symptoms likhein.
                            </div>
                        </div>
                    </div>

                    <form id="chatForm">
                        <div class="input-group">
                            <input type="text" id="message" class="form-control" placeholder="Yahan symptoms likhein..." required>
                            <button class="btn btn-primary">Send</button>
                        </div>
                    </form>

                    <div class="alert alert-warning mt-3 small">
                        ⚠️ Yeh chatbot sirf general guidance ke liye hai.
                        Final diagnosis aur treatment ke liye doctor se consult zaroor karein.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const messageInput = document.getElementById('message');
    const message = messageInput.value.trim();
    if (!message) return;

    const chatBox = document.getElementById('chatBox');

    // User message
    chatBox.innerHTML += `
        <div class="user-msg">
            <div class="bubble user-bubble">${message}</div>
        </div>
    `;
    chatBox.scrollTop = chatBox.scrollHeight;

    messageInput.value = '';

    fetch('chatbot.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(message)
    })
    .then(res => res.json())
    .then(data => {
        chatBox.innerHTML += `
            <div class="bot-msg">
                <div class="bubble bot-bubble">${data.reply}</div>
            </div>
        `;
        chatBox.scrollTop = chatBox.scrollHeight;
    });
});
</script>

</body>
</html>
