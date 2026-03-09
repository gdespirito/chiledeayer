#!/bin/bash
# Script to seed local database with real historical photos of Chile from Wikimedia Commons
# These are public domain images

API_URL="http://chiledeayer.test/api/v1/photos"
TOKEN="1|x9lmHTuHhthMaklcfMNnnBT8WfXQmvunE9pC2nTC59af738a"
TEMP_DIR=$(mktemp -d)

echo "📸 Descargando y subiendo fotos históricas de Chile..."
echo "Directorio temporal: $TEMP_DIR"

upload_photo() {
    local url="$1"
    local filename="$2"
    local description="$3"
    local year_from="$4"
    local year_to="$5"
    local precision="$6"
    local source="$7"
    local tags="$8"

    echo ""
    echo "⬇️  Descargando: $filename"
    curl -sL "$url" -o "$TEMP_DIR/$filename"

    if [ ! -s "$TEMP_DIR/$filename" ]; then
        echo "❌ Error descargando $filename"
        return 1
    fi

    echo "⬆️  Subiendo: $description"
    local tag_args=""
    IFS=',' read -ra TAG_ARRAY <<< "$tags"
    for tag in "${TAG_ARRAY[@]}"; do
        tag_args="$tag_args -F tags[]=$tag"
    done

    local year_to_arg=""
    if [ -n "$year_to" ]; then
        year_to_arg="-F year_to=$year_to"
    fi

    curl -s -X POST "$API_URL" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Accept: application/json" \
        -F "photo=@$TEMP_DIR/$filename" \
        -F "description=$description" \
        -F "year_from=$year_from" \
        $year_to_arg \
        -F "date_precision=$precision" \
        -F "source_credit=$source" \
        $tag_args \
        | python3 -c "import sys,json; d=json.load(sys.stdin); print(f'✅ Foto #{d[\"data\"][\"id\"]} subida')" 2>/dev/null || echo "⚠️  Respuesta recibida"
}

# 1. Alameda de Santiago, 1863
upload_photo \
    "https://upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Alameda_de_Santiago%2C_1863.jpg/1280px-Alameda_de_Santiago%2C_1863.jpg" \
    "alameda-1863.jpg" \
    "Vista de la Alameda de las Delicias, Santiago, hacia 1863. Se aprecian los árboles que bordean el paseo principal de la capital." \
    1863 "" circa \
    "Wikimedia Commons - Dominio público" \
    "Santiago,Alameda,Siglo XIX"

# 2. Catedral de Santiago, 1860
upload_photo \
    "https://upload.wikimedia.org/wikipedia/commons/thumb/5/51/Catedral_Metropolitana_de_Santiago_de_Chile_-_1860.jpg/1024px-Catedral_Metropolitana_de_Santiago_de_Chile_-_1860.jpg" \
    "catedral-1860.jpg" \
    "Catedral Metropolitana de Santiago vista desde la Plaza de Armas, circa 1860. Una de las fotografías más antiguas del templo." \
    1860 "" circa \
    "Wikimedia Commons - Dominio público" \
    "Santiago,Catedral,Plaza de Armas,Arquitectura"

# 3. Alameda circa 1879
upload_photo \
    "https://upload.wikimedia.org/wikipedia/commons/thumb/e/e2/Alameda_Santiago_1879.jpg/1280px-Alameda_Santiago_1879.jpg" \
    "alameda-1879.jpg" \
    "Vista de la Alameda de Santiago, circa 1879. Panorámica del principal paseo de la capital." \
    1879 "" circa \
    "Wikimedia Commons - Dominio público" \
    "Santiago,Alameda,Siglo XIX"

# 4. Alameda de las Delicias, 1912
upload_photo \
    "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c2/Panorama_Alameda_de_las_Delicias_1912.jpg/1280px-Panorama_Alameda_de_las_Delicias_1912.jpg" \
    "alameda-1912.jpg" \
    "Alameda de las Delicias, Santiago, 1912. Vista con tranvías eléctricos y peatones en el corazón de la capital." \
    1912 "" exact \
    "Wikimedia Commons - Dominio público" \
    "Santiago,Alameda,Tranvías,Siglo XX"

# 5. Casino Ross, Viña del Mar
upload_photo \
    "https://upload.wikimedia.org/wikipedia/commons/thumb/a/af/Casino_Ross_%28Postal%29.jpg/1280px-Casino_Ross_%28Postal%29.jpg" \
    "casino-ross.jpg" \
    "Casino Ross en Viña del Mar, circa 1908. Elegante edificio de estilo europeo que fue centro de la vida social viñamarina." \
    1908 "" circa \
    "Wikimedia Commons - Dominio público" \
    "Viña del Mar,Casino Ross,Arquitectura,Valparaíso"

# 6. Municipalidad de Valparaíso
upload_photo \
    "https://upload.wikimedia.org/wikipedia/commons/thumb/3/34/Municipalidad_de_Valpara%C3%ADso.jpg/1024px-Municipalidad_de_Valpara%C3%ADso.jpg" \
    "municipalidad-valparaiso.jpg" \
    "Antigua Municipalidad de Valparaíso, edificio emblemático del puerto. Arquitectura neoclásica del siglo XIX." \
    1890 "" circa \
    "Wikimedia Commons - Dominio público" \
    "Valparaíso,Municipalidad,Arquitectura,Siglo XIX"

# 7. Avenida Pedro Montt, Valparaíso
upload_photo \
    "https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Avenida_Pedro_Montt_in_Valpara%C3%ADso.jpg/1280px-Avenida_Pedro_Montt_in_Valpara%C3%ADso.jpg" \
    "pedro-montt-valparaiso.jpg" \
    "Avenida Pedro Montt en Valparaíso, con tranvías y edificios de época. Vista del plan del puerto." \
    1910 "" circa \
    "Wikimedia Commons - Dominio público" \
    "Valparaíso,Tranvías,Avenida Pedro Montt"

# 8. Parque Cousiño
upload_photo \
    "https://upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Parque_Cousi%C3%B1o_-_Santiago.jpg/1280px-Parque_Cousi%C3%B1o_-_Santiago.jpg" \
    "parque-cousino.jpg" \
    "Parque Cousiño (actual Parque O'Higgins), Santiago. Elegante paseo público de la sociedad santiaguina de principios del siglo XX." \
    1900 "" circa \
    "Wikimedia Commons - Dominio público" \
    "Santiago,Parque Cousiño,Parque O'Higgins"

# 9. Vendedor de brevas
upload_photo \
    "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Vendedor_de_brevas.jpg/800px-Vendedor_de_brevas.jpg" \
    "vendedor-brevas.jpg" \
    "Vendedor de brevas, escena costumbrista del Chile antiguo. Retrato de la vida cotidiana popular." \
    1900 "" circa \
    "Wikimedia Commons - Dominio público" \
    "Costumbrismo,Oficios,Vida cotidiana"

# 10. Estación Central
upload_photo \
    "https://upload.wikimedia.org/wikipedia/commons/thumb/1/1d/Estacion_central_de_Santiago.jpg/1280px-Estacion_central_de_Santiago.jpg" \
    "estacion-central.jpg" \
    "Avenida Alameda, Plaza Argentina y Estación Central de Santiago, con coches a caballo y tranvías." \
    1910 "" circa \
    "Wikimedia Commons - Dominio público" \
    "Santiago,Estación Central,Tranvías,Alameda"

echo ""
echo "🎉 ¡Proceso completado!"
echo "Limpiando archivos temporales..."
rm -rf "$TEMP_DIR"
