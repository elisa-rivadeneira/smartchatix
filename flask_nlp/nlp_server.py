from flask import Flask, request, jsonify, session
import spacy
import re
import logging
from search import buscar_en_faiss  # Importamos FAISS
from nlp.intencion_detector import detectar_intencion_nlp



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
        "ventas": ["muestrame todas las ventas", "como van las ventas", "ventas de"],
        "grafico": ["muestrame un grafico","muestrame la grafica", "muestrame el grafico","grafica las ventas", "mostrar grafico", "visualizar grafico"],
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


    # 🔹 Si la intención es "grafico", buscar datos en FAISS
    if intencion_detectada == "grafico":
        logging.debug(f"✅ la intencion detectada es grafico y el mensaje es  {mensaje}")
        resultados = buscar_en_faiss(mensaje)
        if resultados:
            return jsonify({
                "intencion": "grafico",
                "resultados": resultados
            })
        else:
            return jsonify({"intencion": "grafico", "error": "No se encontraron datos para el gráfico."})


    # 🔹 Si la intención es "venta", buscar datos en FAISS
    if intencion_detectada == "ventas":
        logging.debug(f"✅ la intencion detectada es ventas en faiss y el mensaje es {mensaje}")
        resultados = buscar_en_faiss(mensaje)
        logging.debug(f"✅ Los resultados del faiss {resultados}")

        if resultados:
            return jsonify({
                "intencion": "ventas",
                "resultados": resultados
            })
        else:
            return jsonify({"intencion": "ventas", "error": "No se encontraron datos para la respuesta."})            



def generar_sql(intencion, ids_encontrados, extra_data=None):
    if intencion == "ventas_totales":
        return f"SELECT SUM(total) AS total_ventas FROM ventas WHERE id IN ({','.join(map(str, ids_encontrados))});"

    elif intencion == "ventas_por_producto" and extra_data:
        return f"SELECT SUM(total) AS total_ventas FROM ventas WHERE producto = '{extra_data}' AND id IN ({','.join(map(str, ids_encontrados))});"

    elif intencion == "ventas_por_fecha" and extra_data:
        return f"SELECT SUM(total) AS total_ventas FROM ventas WHERE fecha_venta BETWEEN '{extra_data['inicio']}' AND '{extra_data['fin']}' AND id IN ({','.join(map(str, ids_encontrados))});"

    elif intencion == "ventas_por_cliente" and extra_data:
        return f"SELECT SUM(total) AS total_ventas FROM ventas WHERE cliente_id = {extra_data} AND id IN ({','.join(map(str, ids_encontrados))});"

    elif intencion == "ventas_mayor":
        return f"SELECT * FROM ventas WHERE id IN ({','.join(map(str, ids_encontrados))}) ORDER BY total DESC LIMIT 1;"

    elif intencion == "ventas_promedio":
        return f"SELECT AVG(total) AS promedio_ventas FROM ventas WHERE id IN ({','.join(map(str, ids_encontrados))});"

    elif intencion == "compras_totales":
        return f"SELECT SUM(total) AS total_compras FROM compras WHERE id IN ({','.join(map(str, ids_encontrados))});"

    elif intencion == "compras_por_producto" and extra_data:
        return f"SELECT SUM(total) AS total_compras FROM compras WHERE producto = '{extra_data}' AND id IN ({','.join(map(str, ids_encontrados))});"

    elif intencion == "compras_por_fecha" and extra_data:
        return f"SELECT SUM(total) AS total_compras FROM compras WHERE fecha_compra BETWEEN '{extra_data['inicio']}' AND '{extra_data['fin']}' AND id IN ({','.join(map(str, ids_encontrados))});"

    return None

    # print(f"✅ La intención fue: {intencion_detectada}")
    # print(f"✅ El destinatario es: {destinatario}")

    logging.info(f"✅ La intención fue: {intencion_detectada}")
    logging.debug(f"✅ El destinatario es: {destinatario}")

    return jsonify({
        "intencion": intencion_detectada,
        "destinatario": destinatario
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5002, debug=True)


# ############################
# # 1️⃣ Crear el entorno virtual
# python -m venv venv

# # 2️⃣ Activarlo
# # En Windows:
# venv\Scripts\activate
# # En Mac/Linux:
# source venv/bin/activate

# # 3️⃣ Instalar dependencias desde requirements.txt
# pip install -r requirements.txt
