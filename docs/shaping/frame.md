---
shaping: true
---

# Archivo de Chile — Frame

## Source

> La idea de este nuevo proyecto es crear un archivo de imagenes antiguas de Chile. No tengo muy clara la UI todavía, pero vamos a crear las bases que nos permitirán crear este archivo publico y colaborativo.
>
> Necesito también que una vez que tengamos deployado el sistema en nuestro k8s, vamos a buscar imágenes de chile antiguo, y las vamos a cargar.
>
> Sobre el proyecto, la idea es tener una base de datos de imágenes, con buena metadata. La idea es tener metadata de cada imagen, como el lugar donde se sacó, y la orientación. Muchas veces son fotos de calles donde antes era puro campo, y ver como se vería ahora sería genial. Muchas veces cuesta saber que se está viendo en la imagen, porque todo está muy distinto.
>
> Me gustaría que las fotos de calles, y cosas así, tengan esta meta información, que es distinto a una foto de personajes antiguos, o de un lugar interior.
>
> En las fotos donde el objeto principal sean personas, o algo así, me gustaría que se puedan marcar en la foto, como cuando se podía tagear personas en facebook. Considerando que las fotos son públicas y colaborativas, habrá que buscar una manera de guardar a las personas, y diferenciar entre personajes públicos (como fotos de un ex presidente o celebridad) de personas anónimas o desconocidas.
>
> Me gustaría referenciar también los lugares, para poder filtrar rápidamente por fotos tomadas en el mismo lugar. Esos lugares pueden ya no existir incluso. Para hacer eso, es necesario marcar la ubicación en el mapa de donde estaba el lugar de la foto. Al marcar el lugar de una nueva foto, debería recomendar lugares previamente creados.
>
> Guardar información como una descripción, el año de la foto es básico también. Si una foto es de un lugar específico desconocido, pero se sabe más o menos el lugar, también es válido, pero no sé aún cómo resolverlo. Sería algo así como "en algún lugar de Providencia", y poder marcar un área de referencia actual (una comuna, ciudad o barrio desde Google Maps).
>
> La idea es tener un cargador de imágenes público, donde exista un wizard para poder llenar esta información de manera simple, pudiendo editar los detalles de cada foto cargada, pero que si son todas de un mismo lugar, también sea simple reusar la información sin tener que completarla a mano igual en todas.
>
> Me gustaría armar un mapa también.
>
> Tener un API es importante porque por IA me gustaría revisar muchos sitios que tengan imágenes públicas para ir recopilándolas. Por otro lado, también me gustaría que colaborativamente se pueda llenar información de las imágenes. Puede que tenga una foto donde no sé nada, pero la gente pueda ir sumando información.
>
> Debe haber comentarios, pero solo personas registradas pueden comentar. En el perfil de las personas, pueden ver la opción de cargar las imágenes, y ver las imágenes cargadas. Solo usuarios registrados pueden colaborar en otras imágenes también. Los usuarios anónimos solo pueden ver.

---

## Problem

Las fotografías históricas de Chile están dispersas en múltiples fuentes sin metadata estandarizada. Es difícil identificar qué se ve en las fotos antiguas porque los lugares han cambiado drásticamente. No existe una plataforma colaborativa donde la comunidad pueda contribuir tanto imágenes como conocimiento contextual sobre ellas.

## Outcome

Una plataforma pública y colaborativa donde cualquier persona puede explorar fotografías históricas de Chile con metadata rica (ubicación, orientación, personas, época), y donde usuarios registrados pueden contribuir imágenes y enriquecer la información existente. La plataforma permite geolocalizar las fotos y compararlas con el presente.
