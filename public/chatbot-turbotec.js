function lanzarchat() {
    const chatbotWidget = document.getElementById("chatbot-widget");
    const chatMessages = document.getElementById("chatbot-messages");

    if (chatbotWidget) {
        // Mostrar el widget si está oculto
        chatbotWidget.style.display = "block";

        // Limpia los mensajes previos si es necesario
        chatMessages.innerHTML = "";

        // Muestra un mensaje de bienvenida personalizado
        const welcomeMessage = "¡Hola! ¿En qué puedo ayudarte hoy?";
        const newMessage = document.createElement("div");
        newMessage.className = "chat-message bot";
        newMessage.innerHTML = welcomeMessage;


        chatMessages.appendChild(newMessage);
    } else {
        console.error("El chatbot no está inicializado correctamente.");
    }
}



// Función para iniciar el flujo de prueba gratis
// Función para iniciar el flujo de prueba gratis
function pedirprueba() {
    const chatWidget = document.getElementById("chatbot-widget");
    const chatMessages = document.getElementById("chatbot-messages");

    // Mostrar el widget si está oculto
    if (chatWidget.style.display === "none") {
        chatWidget.style.display = "block";
    }

    // Verificar si ya existe el mensaje inicial del flujo de prueba
    const pruebaMessageExists = Array.from(chatMessages.children).some(
        (child) => child.className === "chat-message bot" && child.innerHTML.includes("¿Quieres empezar tu prueba?")
    );

    if (!pruebaMessageExists) {
        // Agregar mensaje inicial
        const initialMessage = document.createElement("div");
        initialMessage.className = "chat-message bot";
        initialMessage.innerHTML = "¿Quieres empezar tu prueba de 15 días? Por favor, elige una opción:";
        chatMessages.appendChild(initialMessage);

        // Agregar botones "Sí" y "No"
        const buttonContainer = document.createElement("div");
        buttonContainer.className = "chat-options";

        // Botón "Sí"
        const yesButton = document.createElement("button");
        yesButton.className = "chat-option-btn";
        yesButton.innerHTML = "Sí";
        yesButton.onclick = () => handlePruebaResponse(true);
        buttonContainer.appendChild(yesButton);

        // Botón "No"
        const noButton = document.createElement("button");
        noButton.className = "chat-option-btn";
        noButton.innerHTML = "No";
        noButton.onclick = () => handlePruebaResponse(false);
        buttonContainer.appendChild(noButton);

        chatMessages.appendChild(buttonContainer);
    }
}

// Función para manejar la respuesta del usuario al flujo de prueba
function handlePruebaResponse(isYes) {
    const chatMessages = document.getElementById("chatbot-messages");

    // Eliminar botones después de la respuesta
    const options = document.querySelector(".chat-options");
    if (options) {
        options.remove();
    }

    // Mensajes según la respuesta
    if (isYes) {
        const messageYes = document.createElement("div");
        messageYes.className = "chat-message bot";
        messageYes.innerHTML =
            "¡Genial! Para solicitar tu prueba gratuita, por favor contáctanos vía WhatsApp haciendo clic <a href='https://wa.me/1234567890' target='_blank'>aquí</a>.";
        chatMessages.appendChild(messageYes);
    } else {
        const messageNo = document.createElement("div");
        messageNo.className = "chat-message bot";
        messageNo.innerHTML =
            "Entendido. Si tienes alguna consulta sobre nuestros servicios o necesitas ayuda, ¡no dudes en escribirnos!";
        chatMessages.appendChild(messageNo);
    }
}






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

        let sessionId = null; // Valor inicial


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
                body: JSON.stringify({ 
                    user_input: userMessage,
                    session_id: sessionId    // Agregar session_id
                    

                }),
            })
                .then((response) => response.json())
                .then((data) => {
                        // Verificar si la respuesta contiene assistant_response
                        if (data && data.assistant_response) {


                           // const renderedContent = renderContent(data.assistant_response);
                            appendMessage("bot", data.assistant_response);

                            if (data.session_id) {
                                sessionId = data.session_id;
                                console.log('Nuevo session_id asignado:', sessionId);
                            }
                        } else {
                            appendMessage("bot", "Lo siento, no se pudo obtener una respuesta válida.");
                        }
                 //  appendMessage("bot", data.assistant_response);

                    if (data.session_id) {
                        sessionId = data.session_id;
                        console.log('Nuevo session_id asignado:', sessionId);
                    }

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
                console.log('enviando mensaje');

                // Mostrar el indicador de escritura antes de enviar el mensaje
                showTypingIndicator();

                sendMessage();

                // Prevenir el comportamiento por defecto del Enter
                event.preventDefault();

                // Ocultar el indicador de escritura después de un pequeño retraso
                setTimeout(() => {
                    hideTypingIndicator();
                }, 1000);  // Ajusta el tiempo según sea necesario
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
    console.log('funcion togglechat');
    chatWidget.style.display = chatWidget.style.display === "none" ? "block" : "none";

    if (!chatWidget.classList.contains("active")) {
        chatWidget.classList.add("active");
              // Mostrar indicador de escritura al abrir el widget
              
        
        // Si se proporciona un mensaje de bienvenida, se muestra
        if (welcomeMessage) {
            console.log('welcome message and block');

            const welcomeMessageExists = Array.from(chatMessages.children).some(
                (child) => child.className === "chat-message bot" && child.innerHTML === welcomeMessage
            );

            if (!welcomeMessageExists) {
                

                const newMessage = document.createElement("div");
                newMessage.className = "chat-message bot";
                newMessage.innerHTML = welcomeMessage;
                chatMessages.appendChild(newMessage);
    
                console.log('Mensaje de bienvenida agregado');

                


            } else {
                console.log('mensaje SI existe de bienvenida');
                
                
                // Si no existe, agregar el mensaje de bienvenida
                const newMessage = document.createElement("div");
                newMessage.className = "chat-message bot";
                newMessage.innerHTML = "";
          
            }
        }
    } else {
        chatWidget.classList.remove("active");
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


    


        // Agregar un botón para redirigir a WhatsApp
        const whatsappButton = document.createElement("button");
//        chatButton.innerHTML = '<i class="fas fa-comments"></i>';

        whatsappButton.innerHTML  = '<i class="fas fa-comments"></i> Solicitar Prueba Gratuita';
        whatsappButton.style.marginTop = "10px";
        whatsappButton.style.backgroundColor = "#25D366";
        whatsappButton.style.color = "white";
        whatsappButton.style.border = "none";
        whatsappButton.style.padding = "10px";
        whatsappButton.style.borderRadius = "5px";
        whatsappButton.style.cursor = "pointer";
        whatsappButton.onclick = () => {
            window.open("https://wa.me/1234567890?text=¡Hola! Estoy interesado en la prueba gratuita.", "_blank");
        };

        chatMessages.appendChild(whatsappButton);
    });

    function showTypingIndicator() {
        // Verificar si ya existe el indicador de escritura
        if (!document.getElementById("typing-indicator")) {
            // Crear el div del indicador de escritura
            const typingIndicator = document.createElement("div");
            typingIndicator.id = "typing-indicator";
            typingIndicator.innerHTML = '<div class="typing">...</div>';  // Aquí va la animación
    
            // Obtener el contenedor de los mensajes
            const messagesContainer = document.getElementById("chatbot-messages");
            
            // Verificar que el contenedor de mensajes existe
            if (messagesContainer) {
                // Insertar el typingIndicator dentro del contenedor de mensajes
                messagesContainer.appendChild(typingIndicator);
            } else {
                console.error("El contenedor de mensajes no existe.");
            }
        }
    }
    
    
    function hideTypingIndicator() {
        const typingIndicator = document.getElementById("typing-indicator");
        if (typingIndicator) {
            typingIndicator.style.display = "none";
        }
    }



});
