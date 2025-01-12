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


  // Crear el chatbot widget si no existe
  let chatWidget = document.getElementById("chatbot-widget");
  if (!chatWidget) {
      chatWidget = document.createElement("div");
      chatWidget.id = "chatbot-widget";
      chatWidget.style.display = "none";
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
          <div id="chatbot-messages" style="overflow-y: auto; height: 80%;"></div>
          <input type="text" id="chatbot-input" placeholder="Escribe tu mensaje..." style="display: none;" />
          <button id="chatbot-close">Cerrar</button>
      `;
      container.appendChild(chatWidget);
  }


  const chatMessages = document.getElementById("chatbot-messages");
  const chatClose = document.getElementById("chatbot-close");


    // Agregar evento click al botón #start-chatbot
    startChatbotButton.addEventListener("click", () => {
        chatWidget.style.display = "block";
        chatMessages.innerHTML = "";

        // Mensaje inicial con opciones
        appendMessage("bot", "¡Hola! 🎉 Estamos emocionados de que quieras probar nuestro servicio. Primero, elige el plan que más se adapte a tus necesidades:", [
            {
                label: "Plan Básico - US$29/mes",
                action: () => showPlanDetails("Plan Básico", "1 chatbot integrado a tu web, 2000 interacciones mensuales, acceso al historial de chats "),
            },
            {
                label: "Plan Pro - US$59/mes",
                action: () => showPlanDetails("Plan Pro", "3 chatbots integrados a tu web, 10000 interacciones mensuales, acceso al historial de chats"),
            },
            {
                label: "Plan Premium - Consultar",
                action: () => showPlanDetails("Plan Premium", "Chatbot integrados, Flujos Avanzados, Incluye soporte prioritario y funciones avanzadas."),
            },
        ]);

                // Cerrar el chatbot
                chatClose.addEventListener("click", () => {
                    chatWidget.style.display = "none";
                });
        
                // Función para agregar mensajes
                function appendMessage(sender, message, buttons = []) {
                    const messageElement = document.createElement("div");
                    messageElement.className = sender === "user" ? "chat-message user" : "chat-message bot";
                    messageElement.innerHTML = message;
        
                    chatMessages.appendChild(messageElement);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
        
                    // Agregar botones si los hay
                    if (buttons.length > 0) {
                        buttons.forEach((button) => {
                            const buttonElement = document.createElement("button");
                            buttonElement.textContent = button.label;
                            buttonElement.style.marginTop = "10px";
                            buttonElement.style.display = "block";
                            buttonElement.style.backgroundColor = "#4CAF50";
                            buttonElement.style.color = "white";
                            buttonElement.style.border = "none";
                            buttonElement.style.padding = "10px";
                            buttonElement.style.borderRadius = "5px";
                            buttonElement.style.cursor = "pointer";
                            buttonElement.addEventListener("click", button.action);
        
                            chatMessages.appendChild(buttonElement);
                        });
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                }
        
                // Mostrar detalles del plan seleccionado
                function showPlanDetails(planName, planDescription) {
                    chatMessages.innerHTML = "";
                    appendMessage("bot", `Has seleccionado el <b>${planName}</b>. ${planDescription} ¿Te gustaría iniciar una conversación para más detalles?`, [
                        {
                            label: "Sí, hablar con un humano",
                            action: () => {
                                window.open(
                                    `https://wa.me/1234567890?text=¡Hola! Estoy interesado en el ${planName} y quiero más información.`,
                                    "_blank"
                                );
                            },
                        },
                        {
                            label: "Volver a los planes",
                            action: () => {
                                chatMessages.innerHTML = "";
                                startChatbotButton.click(); // Volver a mostrar opciones
                            },
                        },
                    ]);
                }
    });
});
