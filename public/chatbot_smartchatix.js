document.addEventListener("DOMContentLoaded", function () {
    function initChatbot(config) {
        const container = document.querySelector(config.container);
        if (!container) {
            console.error(`El contenedor ${config.container} no se encontró en el DOM.`);
            return;
        }

        // Crear el botón #start-chatbot dinámicamente si no existe
        let startChatbotButton = document.getElementById("start-chatbot");
        if (!startChatbotButton) {
            startChatbotButton = document.createElement("button");
            startChatbotButton.id = "start-chatbot";
            startChatbotButton.textContent = "Iniciar Chat";
            startChatbotButton.style.padding = "10px 20px";
            startChatbotButton.style.backgroundColor = "#007BFF";
            startChatbotButton.style.color = "#FFF";
            startChatbotButton.style.border = "none";
            startChatbotButton.style.borderRadius = "5px";
            startChatbotButton.style.cursor = "pointer";
            startChatbotButton.style.marginTop = "20px";
            container.appendChild(startChatbotButton);
        }

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

        // Mostrar el chatbot
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
                    action: () => showPlanDetails("Plan Pro", "Plan Básico", "3 chatbots integrados a tu web, 10000 interacciones mensuales, acceso al historial de chats"),
                },
                {
                    label: "Plan Premium - Consultar",
                    action: () => showPlanDetails("Plan Premium", "Chatbot integrados, Flujos Avanzados, Incluye soporte prioritario y funciones avanzadas."),
                },
            ]);
        });

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
    }

    // Inicializar chatbot
    initChatbot({
        container: "#chatbot-container",
    });
});
