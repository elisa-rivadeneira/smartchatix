document.addEventListener("DOMContentLoaded", function () {
    function initChatbot(config) {
        console.log("En initChatbot");

        // Crear el contenedor si no existe
        const container = document.querySelector(config.container);
        if (!container) {
            console.error("El contenedor del chatbot no existe.");
            return;
        }

        // Crear el botón del chatbot si no existe
        let chatButton = document.getElementById("chatbot-button");
        if (!chatButton) {
            chatButton = document.createElement("button");
            chatButton.id = "chatbot-button";
            chatButton.innerHTML = '<i class="fas fa-comments"></i>';
            container.appendChild(chatButton);

            // Estilos del botón
            chatButton.style.fontSize = "24px";
            chatButton.style.padding = "15px";
            chatButton.style.position = "fixed";
            chatButton.style.bottom = "20px";
            chatButton.style.right = "20px";
            chatButton.style.zIndex = "1000";
            chatButton.style.backgroundColor = "#4CAF50";
            chatButton.style.color = "white";
            chatButton.style.border = "none";
            chatButton.style.borderRadius = "50px";
        }

        // Crear el widget de chat (inicialmente oculto)
        let chatWidget = document.getElementById("chatbot-widget");
        if (!chatWidget) {
            chatWidget = document.createElement("div");
            chatWidget.id = "chatbot-widget";
            chatWidget.style.display = "none"; // Inicialmente oculto
            chatWidget.style.position = "fixed";
            chatWidget.style.bottom = "80px";
            chatWidget.style.right = "20px";
            chatWidget.style.zIndex = "999";
            chatWidget.style.width = "300px";
            chatWidget.style.height = "400px";
            chatWidget.style.border = "1px solid #ccc";
            chatWidget.style.backgroundColor = "#fff";
            chatWidget.style.boxShadow = "0 0 10px rgba(0, 0, 0, 0.1)";
            chatWidget.style.padding = "10px";
            chatWidget.innerHTML = `
                <div id="chatbot-messages"></div>
                <input type="text" id="chatbot-input" placeholder="Escribe tu mensaje..." />
                <button id="chatbot-send">Enviar</button>
                <button id="chatbot-close">x</button>
            `;
            container.appendChild(chatWidget);
        }

        const chatMessages = document.getElementById("chatbot-messages");
        const chatInput = document.getElementById("chatbot-input");
        const chatSend = document.getElementById("chatbot-send");
        const chatClose = document.getElementById("chatbot-close");

        // Mostrar/Ocultar el widget del chatbot
        chatButton.addEventListener("click", () => {
            toggleChatWidget(chatWidget, config.welcomeMessage);
        });

        // Cerrar el widget del chatbot
        chatClose.addEventListener("click", () => {
            chatWidget.style.display = "none";
        });

        // Función para enviar mensaje
        const sendMessage = () => {
            const userMessage = chatInput.value.trim();
            if (userMessage === "") return;

            // Mostrar el mensaje del usuario en la ventana del chat
            appendMessage("user", userMessage);

            // Limpiar el campo de entrada
            chatInput.value = "";

            // Enviar el mensaje al backend
            fetch(`https://smartchatix.com/api/generate-response/${chatId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ user_input: userMessage }),
            })
                .then((response) => response.json())
                .then((data) => {
                    // Mostrar la respuesta del chatbot
                    appendMessage("bot", data.assistant_response);
                })
                .catch((error) => {
                    console.error("Error:", error);
                    appendMessage("bot", "Lo siento, ocurrió un error. Intenta nuevamente.");
                });
        };

        // Enviar mensaje al hacer clic en el botón de enviar
        chatSend.addEventListener("click", sendMessage);

        // Enviar mensaje al presionar Enter
        chatInput.addEventListener("keydown", (event) => {
            if (event.key === "Enter") {
                sendMessage();
                event.preventDefault();
            }
        });

        // Función para agregar mensajes al chat
        function appendMessage(sender, message) {
            const messageElement = document.createElement("div");
            messageElement.className = sender === "user" ? "chat-message user" : "chat-message bot";
            messageElement.innerHTML = message;

            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Función para mostrar el widget del chatbot y mensaje personalizado
        function toggleChatWidget(chatWidget, welcomeMessage) {
            chatWidget.style.display = chatWidget.style.display === "none" ? "block" : "none";
            if (welcomeMessage && chatWidget.style.display === "block") {
                appendMessage("bot", welcomeMessage);
            }
        }
    }

    // Llamar a initChatbot para el botón general
    initChatbot({
        container: "#chatbot-container",
        welcomeMessage: "¡Hola! ¿En qué puedo ayudarte?",
        botName: "SmartChatix",
    });

    const startChatbotButton = document.getElementById("start-chatbot");

    // Agregar evento click al botón #start-chatbot
    startChatbotButton.addEventListener("click", () => {
        const chatbotWidget = document.getElementById("chatbot-widget");
        const welcomeMessage = "¡Hola! Estoy aquí para ayudarte a comenzar tu prueba gratuita. Si tienes alguna duda, ¡puedes hablar directamente con nuestro equipo! 😊";
        
        // Mostrar el widget y mensaje personalizado
        chatbotWidget.style.display = "block";
        const chatMessages = document.getElementById("chatbot-messages");
        chatMessages.innerHTML = ""; // Limpia mensajes previos
        const newMessage = document.createElement("div");
        newMessage.className = "chat-message bot";
        newMessage.innerHTML = welcomeMessage;
        chatMessages.appendChild(newMessage);

        // Agregar un botón para redirigir a WhatsApp
        const whatsappButton = document.createElement("button");
        whatsappButton.textContent = "Solicitar Prueba Gratuita";
        whatsappButton.style.marginTop = "10px";
        whatsappButton.style.backgroundColor = "#25D366";
        whatsappButton.style.color = "white";
        whatsappButton.style.border = "none";
        whatsappButton.style.padding = "10px";
        whatsappButton.style.borderRadius = "5px";
        whatsappButton.style.cursor = "pointer";
        whatsappButton.onclick = () => {
            window.open("https://wa.me/51983269818?text=¡Hola! Estoy interesado en la prueba gratuita.", "_blank");
        };

        chatMessages.appendChild(whatsappButton);
    });
});
