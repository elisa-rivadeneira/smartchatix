from flask import Flask, request, jsonify, session
import spacy
import re
import logging
from vector_store import buscar_en_faiss  # Importamos FAISS


# Configurar logging para que escriba en la consola y en un archivo
logging.basicConfig(level=logging.DEBUG, format="%(asctime)s - %(levelname)s - %(message)s", handlers=[
    logging.FileHandler("flask.log"),  # Guardar en un archivo
    logging.StreamHandler()  # Mostrar en la consola
])

app = Flask(__name__)
app.secret_key = "clave_secreta"  # Necesario para utilizar 'session'
nlp = spacy.load("es_core_news_sm")  # Modelo de NLP en español

@app.route('/analizar', methods=['POST'])
def analizar():
    data = request.json
    mensaje = data.get("mensaje", "").lower().strip()
    logging.debug(f"✅ Flask recibió mensaje: {mensaje}")

    # Diccionario de intenciones (incluye todas las que ya tenías)
    intenciones = {
        "clientes": ["muestrame todos los clientes"],
        "ventas": ["muestrame todas las ventas"],
        "grafico": ["muestrame un grafico"],
        "saludo": ["hola", "buenos días", "buenas tardes", "buenas noches", "qué tal", "hey", "hello", "saludos"],
        "crear_email": [
            "crear un email", "envia un email", "redacta un mensaje", "redacta un email",
            "crea un email", "redactar un email", "redactar un mensaje",
            "crea un mensaje", "edita un email"
        ],
        "enviar_email": [
            "si por favor", "si", "si, por favor", "si, envialo",
            "si envia", "dale", "si adelante", "claro que si"
        ],
        "ninguno": []
    }


    # Detectar intención
    intencion_detectada = "ninguno"
    prioridad_crear_email = False

    for intencion, palabras in intenciones.items():
        if any(p in mensaje for p in palabras):
            if intencion == "crear_email":
                prioridad_crear_email = True
            if intencion_detectada == "ninguno":
                intencion_detectada = intencion
        if prioridad_crear_email:
            intencion_detectada = "crear_email"
            break

    # Extraer destinatario SÓLO si la intención es crear_email
    # (si te conviene, puedes extraerlo también para otros casos, pero no es usual)
    if intencion_detectada == "crear_email":
        # Limpieza (por si hay 'un email')
        limpio = (
            mensaje.replace("un email", "")
                   .replace("un mensaje", "")
                   .replace("un correo", "")
                   .strip()
        )
        # Tu regex
        match = re.search(
            r"(?:a\s|para\s)(?P<destinatario>[\w\s]+?)(?:\s+con|,|\s+y|\s+sobre|\s+acerca\s+de|\s+de|$)",
            limpio
        )
        if match:
            destinatario = match.group("destinatario").strip()
            # Limpieza extra
            destinatario = re.sub(r'^(?:a|para)\s+', '', destinatario)
            destinatario = destinatario.strip()
            # Guarda en session
            session["destinatario"] = destinatario
        else:
            # Si no encuentra, reusa lo que haya en session, si acaso
            destinatario = session.get("destinatario", "")
    else:
        # Para otras intenciones, reusa lo guardado en la sesión o pon ""
        destinatario = session.get("destinatario", "")

    # print(f"✅ La intención fue: {intencion_detectada}")
    # print(f"✅ El destinatario es: {destinatario}")

    logging.info(f"✅ La intención fue: {intencion_detectada}")
    logging.debug(f"✅ El destinatario es: {destinatario}")

    return jsonify({
        "intencion": intencion_detectada,
        "destinatario": destinatario
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001, debug=True)
