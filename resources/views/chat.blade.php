

<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <style>



        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .chat-container {
            width: 100%;
            max-width: 90%;
            margin: 50px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .chat-header {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .chat-messages {
            height: 620px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .chat-message {
            margin-bottom: 15px;
        }
        .chat-message.user {
            text-align: right;
            color: #1a73e8;
        }
        .chat-message.assistant {
            text-align: left;
            color: #555;
        }
        .chat-input-container {
            display: flex;
        }
        .chat-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
        }
        .send-btn {
            background-color: #1a73e8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            margin-left: 10px;
            cursor: pointer;
        }
        .send-btn:hover {
                    background-color: #1558b0;
                }


             table {
        border-collapse: collapse;
        width: 100%;
        margin: 20px 0;
        font-size: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    th, td {
        padding: 9px;
        border: 1px solid #ddd;
    }

    th {
        background-color: #f4f4f4;
        color: #333;
        font-weight: bold;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }


                
    </style>
    <!-- Incluir marked.js -->
<script src="https://cdn.jsdelivr.net/npm/marked@2.0.0/lib/marked.min.js"></script>
<!-- Incluye el archivo CSS de Highlight.js para el estilo -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/styles/default.min.css"> -->

<!-- Incluye el archivo JS de Highlight.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/highlight.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/styles/atom-one-dark.min.css">



<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        // Resalta todos los bloques de código en la página
        hljs.highlightAll();
    });
</script>

</head>

<body>
<!-- Código de ejemplo con la clase que Highlight.js usará -->
    <div class="chat-container">
        <div class="chat-header">SmartChatix</div>
        <div id="chat-messages" class="chat-messages"></div>
        <div class="chat-input-container">
        <textarea id="user-input" class="chat-input" placeholder="Escribe tu mensaje aquí..."></textarea>
         <button id="send-btn" class="send-btn">Enviar</button>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const apiUrl = "https://www.smartchatix.com/api/generate-response";
        const assistantId = 12; // Cambia al ID de tu asistente en la base de datos

        const chatMessages = document.getElementById('chat-messages');
        const userInput = document.getElementById('user-input');
        const sendBtn = document.getElementById('send-btn');
        
        let sessionId = null; // Aquí almacenaremos el session_id




        // Función para agregar un mensaje al chat
        function addMessage(content, role) {
            console.log('content::::', content);
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('chat-message', role);

            // Convertir el contenido Markdown a HTML
            messageDiv.innerHTML = marked(content);

            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Función para enviar un mensaje al backend
        async function sendMessage() {
            const message = userInput.value.trim();
            if (!message) return;

            // Agregar el mensaje del usuario al chat
            addMessage(message, 'user');
            userInput.value = '';
            console.log('sessionid retorna__ antes::::', sessionId);

            try {
                const response = await fetch(`${apiUrl}/${assistantId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_input: message,
                        session_id: sessionId // Enviar el session_id en la solicitud
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    sessionId = data.session_id;

                    // Mostrar la respuesta del asistente (convertida a Markdown)
                    addMessage(data.assistant_response, 'assistant');
                    console.log('data::::', data);
                    console.log('sessionid retorna::::', sessionId);

                    // Resaltar el código con highlight.js después de que se haya agregado al DOM
                    document.querySelectorAll('pre code').forEach((block) => {
                        hljs.highlightElement(block); // Resaltar el código
                    });

                } else {
                    addMessage("Hubo un error al obtener la respuesta.", 'assistant');
                }
            } catch (error) {
                console.error('Error:', error);
                addMessage("No se pudo conectar al servidor.", 'assistant');
            }
        }

        // Manejar clic en el botón de enviar
        sendBtn.addEventListener('click', sendMessage);

        // Manejar envío con la tecla Enter
        userInput.addEventListener('keydown', (e) => {
            // Si solo se presiona Enter (sin Shift), enviar el mensaje
            if (e.key === 'Enter' && !e.shiftKey) {
                sendMessage();
            } 
            // Si se presiona Shift + Enter, agregar un salto de línea
            else if (e.key === 'Enter' && e.shiftKey) {
                // Previene el comportamiento por defecto de Enter (que es enviar el mensaje)
                e.preventDefault();

                // Inserta un salto de línea en el cursor
                const cursorPosition = userInput.selectionStart;
                userInput.value = userInput.value.slice(0, cursorPosition) + '\n' + userInput.value.slice(cursorPosition);

                // Coloca el cursor después del salto de línea
                userInput.selectionStart = userInput.selectionEnd = cursorPosition + 1;
            }
        });
    </script>
