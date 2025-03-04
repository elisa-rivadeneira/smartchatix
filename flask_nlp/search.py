import faiss
import numpy as np
from sentence_transformers import SentenceTransformer
import pymysql  # Para conectar a MySQL
from datetime import datetime
import re

# 🔹 Función para conectar a la base de datos MySQL
def conectar_db():
    return pymysql.connect(
        host="127.0.0.1",
        port=3306,
        database="tienda_computadoras",
        user="tienda_user",
        password="123456",
        cursorclass=pymysql.cursors.DictCursor
    )

# 🔹 Función para extraer fechas de la consulta
def extraer_fecha(consulta):
    meses = {
        "enero": "01", "febrero": "02", "marzo": "03", "abril": "04",
        "mayo": "05", "junio": "06", "julio": "07", "agosto": "08",
        "septiembre": "09", "octubre": "10", "noviembre": "11", "diciembre": "12"
    }

    año_detectado = None
    mes_detectado = None

    # Buscar un año en la consulta
    año_match = re.search(r"\b(20\d{2})\b", consulta)
    if año_match:
        año_detectado = año_match.group(1)

    # Buscar un mes en la consulta
    for mes_texto, mes_numero in meses.items():
        if mes_texto in consulta.lower():
            mes_detectado = mes_numero
            break

    return año_detectado, mes_detectado

# 🔹 Función principal para buscar en FAISS y SQL
def buscar_en_faiss(query, k=10):
    print(f"🔍 Mensaje en faiss es_________________: {query}")

    # Extraer fecha de la consulta
    año, mes = extraer_fecha(query)

    hoy = datetime.today()
    if año is None:
        año = hoy.year
    if mes is None:
        mes = hoy.month - 1 if hoy.month > 1 else 12
        mes = f"{mes:02d}" 
        if mes == 12:
            año -= 1  # Si el mes es diciembre, restar un año
    print(f"🔍 Año utilizado: {año}, Mes utilizado: {mes}")


  

    print(f"🔍 Año utilizado: {año}, Mes utilizado: {mes}")


    # Conectar a la base de datos
    conn = conectar_db()
    cursor = conn.cursor()

    # ✅ Si hay una fecha en la consulta, hacer INNER JOIN con la tabla tiempo
    if año or mes:
        sql_query = """
            SELECT v.id, v.producto, v.cantidad, v.precio_unitario, v.fecha_venta, v.total
            FROM ventas v
            INNER JOIN tiempo t
            ON v.fecha_venta BETWEEN t.fecha_inicio AND t.fecha_fin
            WHERE 1=1
        """
        params = []

        meses_nombres = {
            "01": "Enero", "02": "Febrero", "03": "Marzo", "04": "Abril",
            "05": "Mayo", "06": "Junio", "07": "Julio", "08": "Agosto",
            "09": "Septiembre", "10": "Octubre", "11": "Noviembre", "12": "Diciembre"
        }

        if año:
            sql_query += " AND t.año = %s"
            params.append(int(año))

        if mes:
            sql_query += " AND t.nombre_mes = %s"
            params.append(meses_nombres[mes])  # Convertir '01' a 'Enero'

        print("🔍 SQL Query generada:", sql_query)
        print("🔍 Parámetros usados:", params)


        cursor.execute(sql_query, tuple(params))
        resultados = cursor.fetchall()

        print("\n🔍 (Consulta SQL con INNER JOIN) Respuesta en tiempo real:")
        for row in resultados:
            print(f"- {row['cantidad']} unidades de {row['producto']} vendidas el {row['fecha_venta']} → Total: ${row['total']}")

        if resultados:
            respuesta = "\n🔍 Ventas encontradas:\n"
            for row in resultados:
                respuesta += f"- {row['cantidad']} unidades de {row['producto']} vendidas el {row['fecha_venta']} → Total: ${row['total']}\n"
        else:
            respuesta = "⚠️ No se encontraron ventas para esa consulta."

        cursor.close()
        conn.close()
        return respuesta

    # 🔹 Si no hay fechas en la consulta, usar FAISS normalmente
    model = SentenceTransformer("all-MiniLM-L6-v2")
    index = faiss.read_index("data/faiss_ventas.bin")
    id_map = np.load("data/id_map.npy", allow_pickle=True).item()

    # Convertir la consulta en embedding
    query_embedding = model.encode(query).astype("float32").reshape(1, -1)

    # Buscar los documentos más cercanos en FAISS
    distances, indices = index.search(query_embedding, k)

    # Obtener los IDs más relevantes
    ids_encontrados = [id_map[idx] for idx in indices[0]]

    # Construir la consulta SQL con los IDs encontrados por FAISS
    sql_query = "SELECT id, producto, cantidad, precio_unitario, fecha_venta, total FROM ventas WHERE id IN %s"
    params = [tuple(ids_encontrados)]
    
    cursor.execute(sql_query, tuple(params))
    resultados = cursor.fetchall()

    # Cerrar la conexión a la base de datos
    cursor.close()
    conn.close()

    # Mostrar los resultados
    print("\n🔍 Respuesta en tiempo real (FAISS + SQL):")
    for row in resultados:
        print(f"- {row['cantidad']} unidades de {row['producto']} vendidas el {row['fecha_venta']} → Total: ${row['total']}")

    return resultados

if __name__ == "__main__":
    consulta = "VENTAS DE ENERO DEL 2025"  # 🔍 Aquí puedes cambiar la consulta
    buscar_en_faiss(consulta)
