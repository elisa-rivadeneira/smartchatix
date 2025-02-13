<html>
<head>
    <title>2SmartChatix</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" ></script>

    <style>

.dropdown-menu {
  position: static; /* Remove absolute positioning */
  display: none; /* Initially hide the menu */
  padding: 0; /* Remove default padding */
  margin: 0; /* Remove default margin */
  list-style: none; /* Remove default list styling */
  /*background-color: #fff; */
  background-color: transparent; /* Elimina el fondo blanco */

  z-index: 999;
  transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
}

.dropdown-menu.show {
  opacity: 1;
  visibility: visible;
  display: flex; /* Display menu items inline */
  align-items: center; /* Align menu items vertically */
  color: #fff;
  z-index: 1200;
  background: #000;
  background-color: rgba(0, 0, 0, 0.9); /* Black background with opacity */
}

.navbar-nav {
  /* Add margin-right to push menu items to the right */
  margin-right: auto;
}

.nav-item {
  /* Remove margin for inline display */
  margin: 0;
}

.nav-link {
  /* Adjust padding for better spacing */
  padding: 0.5rem 1rem;
}    

.thebest {
  position: relative;
  height: 70vh;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column; /* Apila elementos en vertical para móviles */

}

.best-content{
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 70%;
  color:#fff;
  padding:30px;
  flex-direction: column; /* Apila elementos en vertical para móviles */
  text-align: center; /* Centra el contenido en pantallas pequeñas */
}


.best-container {
  flex: 1;
  padding: 4%; /* Ajusta el padding para crear márgenes */

}


.video-overlay-content p {
  flex: 1;
 /*  background-color: rgba(0, 0, 0, 0.5);  con 50% de transparencia */
    padding:15px;
    line-height: 1.8;
    font-size:1.3em;
    background:#fff;
    color:#0a0a0a;

}

.video-overlay-content h2 {
  flex: 1;
 /*  background-color: rgba(0, 0, 0, 0.5);  con 50% de transparencia */
    padding:15px;
    line-height: 1.8;
    background:#fff;
    color:#0a0a0a;

}


.video-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(2, 11, 66, 0.8); /* Capa negra con 50% de opacidad */
  z-index: 1;
}

.video-overlay-content {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 2;
  color: #fff;
  text-align: center;
  padding: 20px;
}


.navbar {
    background-color: #222; /* Color de fondo del menú */
    text-align: center; /* Centra el título */
}

.navbar-nav {
    display: flex;
    flex-direction: column;
    align-items: center; /* Centra los elementos verticalmente */
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    padding: 5px;
}

.nav-link {
    color: #fff; /* Color del texto */
    text-decoration: none;
    transition: background-color 0.3s ease-in-out;
}

.nav-link:hover {
    background-color: #333; /* Color de fondo al pasar el cursor */
}


@media (min-width: 768px) and (max-width: 1024px) {
    .video-overlay-content h2 {
    font-size: 1.5em; /* Ajustar tamaño de fuente para móviles */
  }

  .video-overlay-content p {
    font-size: 1em; /* Ajustar tamaño de texto para móviles */
    line-height: 1.4;
  }

  .video-overlay-content {
    padding: 15px;
    transform: none;
    top: 15%;
    left: auto;
  }
  .thebest {
    padding: 20px;
    height: 100vh;

  }

}

/* Estilos para pantallas pequeñas (mobile) */
@media (max-width: 768px) {
  .thebest {
    padding: 20px;
    height: 100vh;

  }

  .video-overlay-content {
    padding: 15px;
    transform: none;
    top: 15%;
    left: auto;
  }



  .video-overlay-content h2 {
    font-size: 1.5em; /* Ajustar tamaño de fuente para móviles */
  }

  .video-overlay-content p {
    font-size: 1em; /* Ajustar tamaño de texto para móviles */
    line-height: 1.4;
  }
}

.video-container {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.videobest-bg {
  width: 100%;
  height: 100%;
  object-fit: cover;
}






.hero {
  position: relative;
  height: 100vh;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
}

.video-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: -2;
}

.video-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* Oscurece el video */
  z-index: -1;
}

.hero-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  text-align: left;
  width: 90%; /* Ajusta el ancho del contenido */
  max-width: 1200px; /* Limita el ancho máximo */
  z-index: 1;
  gap: 2rem; /* Espacio entre el texto y la imagen */


}

.text-container {
  flex: 1;
  padding: 20px;
  animation: fadeInLeft 1.5s ease-in-out; /* Animación al cargar */
}

.text-container h1 {
  font-size: 3rem;
  margin-bottom: 20px;
}

.text-container p {
  font-size: 1.2rem;
  line-height: 1.5;
  margin-bottom: 20px;
  background-color: rgba(0, 0, 0, 0.6); /* Fondo semi-transparente */
  padding: 10px 20px;
  border-radius: 5px;
}

.bot-image {
    max-height: 500px;  /* Limita la altura máxima de la imagen a un valor fijo */
    width: auto;      /* Mantiene la proporción de la imagen */
  flex-shrink: 0;   /* Evita que la imagen se reduzca demasiado */  animation: fadeInRight 1.5s ease-in-out; /* Animación al cargar */
}

.btn-primary {
  background-color: #FF1493; /* Fucsia vibrante */
  border: none;
  padding: 15px 30px;
  border-radius: 25px;
  font-size: 1.1rem;
  font-weight: bold;
  color: #fff;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

.btn-primary:hover {
  background-color: #FF69B4; /* Tono más claro al pasar el mouse */
  transform: translateY(-5px);
  box-shadow: 0 6px 10px rgba(0, 0, 0, 0.4);
}

/* Animaciones */
@keyframes fadeInLeft {
  from {
    opacity: 0;
    transform: translateX(-50px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fadeInRight {
  from {
    opacity: 0;
    transform: translateX(50px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}


/* Media query para dispositivos móviles */
@media (max-width: 768px) {
  .hero-content {
    flex-direction: column; /* Organiza los elementos en columna para pantallas pequeñas */
    text-align: center; /* Centra el texto */
    justify-content: center; /* Centra los elementos verticalmente */
    padding: 20px; /* Asegura que los márgenes sean adecuados en pantallas pequeñas */
  }

  .text-container {
    padding: 10%;
  }

  .text-container h1 {
    font-size: 1.5rem; /* Ajusta el tamaño de la fuente del título */
  }

  .text-container p {
    font-size: 1rem; /* Ajusta el tamaño de la fuente del párrafo */
  }

  /* Ocultar la imagen del bot en pantallas pequeñas */
  .bot-image {
    display: none; /* Ocultar la imagen */
  }

  .btn-primary {
    padding: 12px 25px; /* Ajusta el tamaño del botón */
    font-size: 1rem; /* Ajusta el tamaño de la fuente del botón */
  }
}












        /* Free Trial Section */
        .free-trial {
            padding: 50px 20px;
            background-color: #b00465; /* Rosa vibrante */
            background-image:url('images/fondo_rosita.jpg');
            text-align: center;
        }

        .free-trial h3 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 20px;
        }

        .free-trial p {
            font-size: 1.2rem;
            color: #fff;
            margin-bottom: 30px;
        }

        .free-trial .cta-btn {
            background-color: #00a83b; /* Morado */
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .free-trial h3 {
            font-size: 1.5rem;
        }
        .free-trial p {
            font-size: 1rem;

        }

        }





            .section-blue {
                background-color: #007bff;
            }

     
        a {
            color: #ffffff;
        }
        a:hover {
            text-decoration: none;
        }
        .btn {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background-color: #0069d9;
        }
        .cta-section {
            background-color: #171717;
            padding: 50px 0;
        }
        .cta-section h2 {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .cta-section p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        .cta-section .btn {
            font-size: 18px;
            padding: 15px 30px;
        }
        .features-section {
            background-color: #fff;
            padding: 50px 0;
        }
        .features-section h2 {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #222222;
        }
        .features-section .feature-box {
            background-color: #ad0062;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            color:#fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.5); /* Sombra suave */
            border: 1px solid #910655; /* Borde gris claro */

        }
        .features-section .feature-box h3 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .features-section .feature-box p {
            font-size: 16px;
        }

        .feature-box {
            height: 450px; /* Ajusta el valor según tus necesidades */
            }
        .feature-box img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;

        }

        .pricing-section .pricing-table {
    background-color: rgba(255, 255, 255, 0.9); /* Fondo blanco con 50% de transparencia */
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
    color: #000000 !important;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: auto;
    /* max-width: 350px; /* Altura mínima */
    min-height: 400px; /* Altura mínima */
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); /* Sombra suave */
    border: 1px solid #ddd; /* Borde gris claro */
}

.pricing-section .pricing-table ul.features {
    list-style: none;
    padding: 0;
    text-align: center; /* Alineación a la izquierda */
    margin-top: 20px;
}

.pricing-section .pricing-table ul.features li {
    margin-bottom: 10px;
    font-size: 16px;
}

.pricing-section .pricing-table p {
    margin-bottom: 10px;
    font-size: 16px;
    font-weight:bold;

}

.pricing-section .pricing-table ul.features li i {
    color: #28a745; /* Verde para el check */
    margin-right: 8px;
    font-size: 16px;
}

.pricing-section .pricing-table .btn {
    margin-top: 15px;
}

.pricing-section .pricing-table .price {
    font-size: 24px;
    font-weight: bold;
    margin: 15px 0;
}

.pricing-section{
    padding-top:50px;
    color:#fff;
       background-image:url('images/bgcerebro.jpg');

}

.row {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch; /* Asegura que todas las tablas sean iguales en altura */
}

        .testimonials-section {
            background-color: #111111;
            padding: 50px 0;
        }
        .testimonials-section h2 {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .testimonials-section .testimonial-box {
            background-color: #222222;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .testimonials-section .testimonial-box img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .testimonials-section .testimonial-box h3 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .testimonials-section .testimonial-box p {
            font-size: 16px;
        }
        .contact-section {
            background-color: #171717;
            padding: 50px 0;
        }
        .contact-section h2 {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .contact-section form {
            max-width: 500px;
            margin: 0 auto;
        }
        .contact-section form input, .contact-section form textarea {
            width: 100%;
            padding: 10px;
            border: none;
            border-bottom: 2px solid #ffffff;
            margin-bottom: 20px;
        }
        .contact-section form button {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .contact-section form button:hover {
            background-color: #0069d9;
        }
/* Footer styles */
footer {
  background: linear-gradient(to right, #333 0%, #111 100%); /* Dark gradient background */
  padding: 30px 0; /* Adjust padding as needed */
  display: flex; /* Center content horizontally */
  justify-content: center; /* Align content in the center */
  align-items: center; /* Vertically center content */
}

footer p {
  font-size: 16px; /* Increase font size slightly */
  color: #fff; /* White text */
  margin: 0; /* Remove default margin */
}

footer a {
  color: #fff; /* White text for links */
  text-decoration: none; /* Remove underline */
  margin-left: 10px; /* Add spacing between links */
}

footer a:hover {
  opacity: 0.8; /* Slightly reduce opacity on hover */
}

.fab { /* Target Font Awesome icons */
  font-size: 20px; /* Increase icon size */
}
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="#">SmartChatix - Chatbot Personalizados</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse dropdown-menu" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#features">Características</a>
                        </li>
                          <li class="nav-item">
                            <a class="nav-link" href="elixa_english">Elixa English</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#integracion">Integracion a tus Sistemas</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="lanzarchat()">Contacto</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <section class="hero">
  <div class="video-overlay"></div>
  <video autoplay muted loop poster="bg-smartchix.jpg" class="video-bg">
    <source src="images/video-ia.webm" type="video/webm">
    Your browser does not support the video tag.
  </video>

  <div class="hero-content animate">
    <div class="text-container">
      <h1>Chatbots personalizados para tu negocio</h1>
      <p>Optimiza tus procesos, mejora la experiencia de los clientes y eleva tus ventas con soluciones inteligentes diseñadas para tu empresa.</p>
      <a href="#contact" id="start-chatbot2" class="btn btn-primary" onclick='pedirprueba()'>Pide tu prueba gratuita</a>
    </div>
    <img src="images/chatbot.png" alt="Imagen de un bot" class="bot-image">
  </div>
</section>

<section class="features-section">
    <div class="container" id="features">
        <h2>Características</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-box">
                    <img src="images/personalizacion.jpg" alt="Icono de personalización">
                    <h3>Personalización</h3>
                    <p>Creamos chatbots personalizados que se adaptan a las necesidades de tu negocio.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <img src="images/ia.jpg" alt="Icono de inteligencia artificial">
                    <h3>Inteligencia artificial</h3>
                    <p>Utilizamos inteligencia artificial para crear chatbots que pueden aprender y mejorar con el tiempo.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <img src="images/integracion.jpg" alt="Icono de integración">
                    <h3>Integración fácil</h3>
                    <p>Nuestros chatbots se pueden integrar fácilmente en tu sitio web o aplicación.</p>
                </div>
            </div>
        </div>
    </div>
</section>




<section class="pricing-section">
    <div class="container">
        <!-- <h2>Precios</h2> -->
        <div class="row">
            <div class="col-md-12">
                <div class="pricing-table" id="integracion">
                    <h2>Chatbots Personalizados</h2><h3>Integración a tus Sistemas</h3>
                    <p>Soluciones avanzadas con integración a sistemas propios y personalización a medida</p>
                    <ul class="features">
                        <li><i class="fas fa-check"></i> Integración fluida con tu intranet y páginas web, adaptándose a tus plataformas existentes para maximizar su funcionalidad</li>
                        <li><i class="fas fa-check"></i> Flujos avanzados con menús interactivos y opciones personalizables para guiar a tus usuarios de forma intuitiva</li>
                        <li><i class="fas fa-check"></i> Acceso completo al historial de conversaciones, con detalles de mensajes, horarios de inicio de las interacciones</li>
                        <li><i class="fas fa-check"></i> Transfiere conversaciones del chatbot a WhatsApp para una atención humana personalizada y sin interrupciones</li>
                        <li><i class="fas fa-check"></i> Soporte Humano Dedicado: Recibe atención personalizada de un experto que conoce a detalle tu configuración y necesidades</li>

                    </ul>
                    <a href="#contact" id="start-chatbot3" class="btn btn-primary" onclick="pedirprueba()">Solicitar Prueba Gratis</a>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="thebest">

<div class="video-overlay-content">
  <!-- Aquí va tu contenido encima del video -->
  <h2>Transforma tu negocio con chatbots inteligentes 24/7</h2>
  <p>Los chatbots disponibles las 24 horas están revolucionando cómo las organizaciones y negocios gestionan procesos, mejorando la eficiencia y 
    reduciendo tiempos de espera. </br> </br> Desde automatizar tareas internas hasta gestionar interacciones con clientes, cada chatbot puede ser configurado para cumplir con una misión específica, ya sea en la gestión de documentación, atención a consultas o soporte técnico. 
    </br></br>
    Con la posibilidad de integrar múltiples chatbots, tu empresa puede optimizar áreas clave, brindando una experiencia fluida y mejorando la productividad en todo momento</p>
</div>
</section>






    <section class="free-trial" id="trial">
        <h3>Prueba Gratis por 15 Días</h3>
        <p>¡Sin compromiso! Prueba nuestro servicio durante 15 días de forma gratuita y sin tarjeta de crédito.</p>
        <button id="start-chatbot" class="cta-btn">Comienza tu prueba gratis</button>
    </section>


    <div id="chatbot-container"></div>
	<link rel="stylesheet" href="https://smartchatix.com/chatbot.css">
		<script>
			var chatId = 13; // Convierte el valor de $id a un formato seguro para JS
		</script>
		<script src="https://smartchatix.com/chatbot.js"></script>

    <footer>
        <div class="container">
            <p>&copy; SmartChatix 2024</p>
            <a href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.twitter.com/" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram"></i></a>
        </div>
    </footer>
</body>
</html>