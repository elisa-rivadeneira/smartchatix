<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1SmartLanding Page</title>
    <style>
        /* Reset */
        body, h1, h2, h3, h4, p, ul, li, nav {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body */
        body {
            font-family: 'Arial', sans-serif;
            color: white;
            background-color: #1d1d1d;
        }

        /* Header */
        header {
            background: linear-gradient(135deg, #ff5f6d, #ffc371); /* Gradiente vibrante de rosa a amarillo */
            color: white;
            padding: 15px 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        header .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
        }

        nav ul {
            list-style: none;
            display: flex;
        }

        nav ul li {
            margin: 0 20px;
        }

        nav ul li a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            transition: color 0.3s;
        }

        nav ul li a:hover {
            color: #ff5f6d; /* Rosa vibrante */
        }

        /* Hero Section */
        .hero {
            height: 80vh;
            background: linear-gradient(135deg, #000000, #5a2d82, #6c3db7); /* Gradiente vibrante de negro a morado */
            background-image:url(images/bg-smartchatix.jpg);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            padding: 0 20px;
        }

        .hero h2 {
            font-size: 3rem;
            color: white;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.2rem;
            color: #dcdcdc;
            margin-bottom: 30px;
        }

        .cta-btn {
            padding: 15px 30px;
            background-color: #ff5f6d; /* Rosa vibrante */
            color: white;
            text-transform: uppercase;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .cta-btn:hover {
            background-color: #6c3db7; /* Morado */
        }

        /* About Section */
        .about {
            padding: 50px 20px;
            background-color: #2d3a47; /* Gris oscuro */
            text-align: center;
        }

        .about h3 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 20px;
        }

        .about p {
            font-size: 1.2rem;
            color: #ccc;
        }

        /* Plans Section */
        .plans {
            padding: 50px 20px;
            background-color: #5a2d82; /* Morado vibrante */
            text-align: center;
        }

        .plans h3 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 30px;
        }

        .plan-card {
            background-color: #4b1d68;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            color: white;
            display: inline-block;
            width: 250px;
            text-align: center;
            transition: transform 0.3s;
        }

        .plan-card:hover {
            transform: translateY(-10px);
        }

        .plan-card h4 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .plan-card .price {
            font-size: 2rem;
            font-weight: bold;
            color: #ff5f6d; /* Rosa */
        }

        .plan-card .cta-btn {
            margin-top: 15px;
            background-color: #6c3db7; /* Morado */
        }

        /* Free Trial Section */
        .free-trial {
            padding: 50px 20px;
            background-color: #ff5f6d; /* Rosa vibrante */
            text-align: center;
        }

        .free-trial h3 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 20px;
        }

        .free-trial p {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 30px;
        }

        .free-trial .cta-btn {
            background-color: #6c3db7; /* Morado */
        }

        /* Footer */
        footer {
            background-color: #1d1d1d;
            color: white;
            text-align: center;
            padding: 20px 0;
        }

        footer p {
            margin: 0;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            .hero h2 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .cta-btn {
                font-size: 1rem;
                padding: 12px 25px;
            }

            nav ul {
                flex-direction: column;
                margin-top: 10px;
            }

            nav ul li {
                margin: 10px 0;
            }

            .plan-card {
                width: 100%;
                margin: 15px 0;
            }

            .plans {
                padding: 30px 10px;
            }
        }

        /* For Small Screens */
        @media (max-width: 480px) {
            header .logo {
                font-size: 1.5rem;
            }

            .hero h2 {
                font-size: 2rem;
            }

            .about h3,
            .plans h3,
            .free-trial h3 {
                font-size: 2rem;
            }

            .cta-btn {
                padding: 12px 20px;
            }
        }

    </style>
</head>
<body>

    <header>
        <div class="logo">Logo IA</div>
        <nav>
            <ul>
                <li><a href="#home">Iinicio</a></li>
                <li><a href="#about">Acerca de</a></li>
                <li><a href="#plans">Planes</a></li>
                <li><a href="#trial">Prueba Gratis</a></li>
                <li><a href="#contact">Contacto</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero" id="home">
        <div>
            <h2>Asistentes Virtuales Inteligentes</h2>
            <p>Transforma tu negocio con nuestra tecnología de IA. ¡Más rápido, más inteligente y más eficiente!</p>
            <button class="cta-btn">Comienza Ahora</button>
        </div>
    </section>

    <section class="about" id="about">
        <h3>¿Por qué elegirnos?</h3>
        <p>Ofrecemos la mejor tecnología de Inteligencia Artificial adaptada a tus necesidades, con soporte y herramientas para que crezcas.</p>
    </section>

    <section class="plans" id="plans">
        <h3>Planes</h3>
        <div class="plan-card">
            <h4>Plan Básico</h4>
            <p>Ideal para pequeñas empresas o emprendedores.</p>
            <div class="price">$29/mes</div>
            <button class="cta-btn">Suscríbete</button>
        </div>
        <div class="plan-card">
            <h4>Plan Profesional</h4>
            <p>Perfecto para negocios en crecimiento.</p>
            <div class="price">$59/mes</div>
            <button class="cta-btn">Suscríbete</button>
        </div>
        <div class="plan-card">
            <h4>Plan Empresarial</h4>
            <p>Solución avanzada para grandes empresas.</p>
            <div class="price">$99/mes</div>
            <button class="cta-btn">Suscríbete</button>
        </div>
    </section>

    <section class="free-trial" id="trial">
        <h3>Prueba Gratis por 30 Días</h3>
        <p>¡Sin compromiso! Prueba nuestro servicio durante 30 días de forma gratuita y sin tarjeta de crédito.</p>
        <button class="cta-btn">Comienza tu prueba gratis</button>
    </section>

    <footer id="contact">
        <p>&copy; 2024 Todos los derechos reservados.</p>
    </footer>

</body>
</html>
