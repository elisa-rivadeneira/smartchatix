from sentence_transformers import SentenceTransformer
import numpy as np

# Cargar modelo de embeddings
model = SentenceTransformer("all-MiniLM-L6-v2")

# 🔹 Diccionario de intenciones con descripciones más naturales
intenciones_descripciones = {
    "listar_clientes": "Mostrar la lista de clientes registrados.",
    "buscar_cliente": "Buscar información detallada de un cliente específico.",
    "ventas_totales": "Consultar el total de ventas realizadas en un período determinado.",
    "ventas_por_producto": "Consultar la cantidad de productos vendidos de un tipo específico.",
    "ventas_por_fecha": "Obtener las ventas en un período de tiempo.",
    "ventas_por_cliente": "Consultar las ventas hechas a un cliente en particular.",
    "ventas_mayor": "Saber cuál fue la venta más grande registrada.",
    "ventas_promedio": "Calcular el promedio de ventas en un período.",
    "grafico_ventas": "Generar un gráfico visual de ventas.",
    "grafico_compras": "Generar un gráfico visual de compras.",
    "compras_totales": "Consultar el total de compras realizadas.",
    "compras_por_producto": "Consultar la cantidad de productos comprados de un tipo específico.",
    "compras_por_fecha": "Obtener las compras en un período de tiempo.",
}

# Convertir descripciones de intenciones a embeddings
intencion_embeddings = {key: model.encode(desc) for key, desc in intenciones_descripciones.items()}

def detectar_intencion_nlp(pregunta):
    """Detecta la intención de la pregunta basándose en embeddings."""
    pregunta_embedding = model.encode(pregunta)

    # Comparar con todas las intenciones usando similitud de coseno
    similitudes = {intencion: np.dot(pregunta_embedding, emb) for intencion, emb in intencion_embeddings.items()}

    # Obtener la intención con la mayor similitud
    mejor_intencion = max(similitudes, key=similitudes.get)

    return mejor_intencion
