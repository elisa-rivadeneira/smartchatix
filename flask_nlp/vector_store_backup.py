import faiss
import numpy as np
from sentence_transformers import SentenceTransformer
import pymysql  # ✅ Importar pymysql en lugar de psycopg2
import os
import calendar
from datetime import datetime, date  # ✅ Importar date correctamente



# Conectar a MySQL en lugar de PostgreSQL
conn = pymysql.connect(
    host="127.0.0.1",
    port=3306,  # Puerto de MySQL
    database="tienda_computadoras",
    user="tienda_user",
    password="123456",
    cursorclass=pymysql.cursors.DictCursor  # Opcional, devuelve los resultados como diccionarios
)



cursor = conn.cursor()


# 🔹 Convertir los datos en embeddings
model = SentenceTransformer("all-MiniLM-L6-v2")

# 🔹 Obtener datos reales de MySQL
cursor.execute("SELECT id, producto, cantidad, precio_unitario, fecha_venta, total FROM ventas;")
ventas = cursor.fetchall()


def convertir_fecha_texto(fecha):
    # ✅ Asegurar que la fecha es una cadena antes de dividirla
    if isinstance(fecha, date):
        fecha = fecha.strftime("%Y-%m-%d")  # Convertir a string con formato YYYY-MM-DD

    año, mes, dia = fecha.split("-")

    meses_texto = {
        "01": "enero", "02": "febrero", "03": "marzo", "04": "abril",
        "05": "mayo", "06": "junio", "07": "julio", "08": "agosto",
        "09": "septiembre", "10": "octubre", "11": "noviembre", "12": "diciembre"
    }

    return f"{dia} de {meses_texto[mes]} del {año}"


# 🔹 Crear un documento textual por cada venta (para embeddings)
documentos = [f"Venta del {convertir_fecha_texto(row['fecha_venta'])}: {row['cantidad']} unidades de {row['producto']} a ${row['precio_unitario']} cada una. Total: ${row['total']}"
              for row in ventas]

embeddings = np.array([model.encode(doc) for doc in documentos], dtype="float32")

# 🔹 Crear y guardar el índice FAISS
dimension = embeddings.shape[1]
index = faiss.IndexFlatL2(dimension)
index.add(embeddings)

faiss.write_index(index, "faiss_ventas.bin")
print("✅ Índice FAISS creado con datos reales.")

# 🔹 Guardar el mapeo ID ↔ texto
id_map = {i: row["id"] for i, row in enumerate(ventas)}



# 🔹 Asegurar que la carpeta 'data/' existe
if not os.path.exists("data"):
    os.makedirs("data")

# 🔹 Guardar el mapeo ID ↔ índice FAISS
np.save("data/id_map.npy", id_map)

print("✅ id_map.npy guardado correctamente.")
