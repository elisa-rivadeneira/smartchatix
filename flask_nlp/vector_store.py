import faiss
import numpy as np
from sentence_transformers import SentenceTransformer
import pymysql
import os
from datetime import datetime, date
import logging

# 🔹 Función para conectar a MySQL
def conectar_db():
    return pymysql.connect(
        host="127.0.0.1",
        port=3306,
        database="tienda_computadoras",
        user="tienda_user",
        password="123456",
        cursorclass=pymysql.cursors.DictCursor
    )

# 🔹 Cargar modelo de embeddings
model = SentenceTransformer("all-MiniLM-L6-v2")

# 🔹 Cargar FAISS y el mapeo de IDs
index = faiss.read_index("data/faiss_ventas.bin")
id_map = np.load("data/id_map.npy", allow_pickle=True).item()

# 🔹 Función para buscar en FAISS
def buscar_en_faiss(mensaje, k=10):
    # Convertir la consulta en embedding
    logging.info(f"✅Dentro de la funcion faisss")
    query_embedding = model.encode(mensaje).astype("float32").reshape(1, -1)

    # Buscar los documentos más cercanos en FAISS
    distances, indices = index.search(query_embedding, k)

    # Obtener los IDs más relevantes
    ids_encontrados = [id_map[idx] for idx in indices[0]]

    # Conectar a MySQL y obtener datos reales
    conn = conectar_db()
    cursor = conn.cursor()
    cursor.execute("SELECT fecha_venta, total FROM ventas WHERE id IN %s ORDER BY fecha_venta ASC", (tuple(ids_encontrados),))
    resultados = cursor.fetchall()
    cursor.close()
    conn.close()

    return resultados if resultados else None

# 🔹 Si el script se ejecuta directamente, genera FAISS
if __name__ == "__main__":
    conn = conectar_db()
    cursor = conn.cursor()

    # Obtener datos reales de MySQL
    cursor.execute("SELECT id, producto, cantidad, precio_unitario, fecha_venta, total FROM ventas;")
    ventas = cursor.fetchall()

    def convertir_fecha_texto(fecha):
        if isinstance(fecha, date):
            fecha = fecha.strftime("%Y-%m-%d")  # Convertir a string con formato YYYY-MM-DD

        año, mes, dia = fecha.split("-")

        meses_texto = {
            "01": "enero", "02": "febrero", "03": "marzo", "04": "abril",
            "05": "mayo", "06": "junio", "07": "julio", "08": "agosto",
            "09": "septiembre", "10": "octubre", "11": "noviembre", "12": "diciembre"
        }

        return f"{dia} de {meses_texto[mes]} del {año}"

    # Crear embeddings
    documentos = [f"Venta del {convertir_fecha_texto(row['fecha_venta'])}: {row['cantidad']} unidades de {row['producto']} a ${row['precio_unitario']} cada una. Total: ${row['total']}"
                  for row in ventas]

    embeddings = np.array([model.encode(doc) for doc in documentos], dtype="float32")

    # Crear y guardar el índice FAISS
    dimension = embeddings.shape[1]
    index = faiss.IndexFlatL2(dimension)
    index.add(embeddings)

    if not os.path.exists("data"):
        os.makedirs("data")

    faiss.write_index(index, "data/faiss_ventas.bin")
    np.save("data/id_map.npy", {i: row["id"] for i, row in enumerate(ventas)})

    print("✅ Índice FAISS creado con datos reales.")
    print("✅ id_map.npy guardado correctamente.")
