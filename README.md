# Friends4You

Friends4You es un proyecto desarrollado en Angular como parte del curso de Desarrollo de Aplicaciones Web (DAW). La idea es crear una aplicación web moderna y dinámica que conecte a usuarios de manera sencilla e intuitiva. Puedes ver la estructura completa del proyecto en mi repositorio de GitHub: [Friends4You](https://github.com/samu-tec/Friends4You).

## Descripción

Friends4You es una red social diseñada para ayudar a los usuarios a encontrar amigos cercanos y organizar encuentros en lugares como cafeterías, parques o eventos locales. La aplicación facilita la conexión entre personas con intereses similares y promueve la interacción social en la vida real.

Esta aplicación está diseñada para ofrecer una experiencia interactiva y modular. Gracias a Angular, se implementa un enfoque basado en componentes que facilita la reutilización del código y la integración de nuevos servicios. Además, se utilizan técnicas de diseño responsive para asegurar que la interfaz se vea genial en cualquier dispositivo.

## Características

- **Angular Framework:** Construido con Angular para aprovechar sus potentes características y modularidad.
- **Componentes Reutilizables:** Cada parte de la aplicación está dividida en componentes que simplifican el mantenimiento y la escalabilidad.
- **Servicios y APIs:** Se integran servicios para gestionar peticiones HTTP y trabajar con datos de fuentes externas.
- **Routing Eficiente:** Navegación fluida entre secciones gracias al sistema de enrutamiento de Angular.
- **Diseño Responsive:** Adaptación completa a diferentes tamaños de pantalla, asegurando una experiencia óptima en móviles y ordenadores.
- **Búsqueda de Amigos Cercanos:** Los usuarios pueden encontrar personas cercanas con intereses en común.
- **Sistema de Eventos:** Posibilidad de organizar y unirse a quedadas en cafeterías, parques y otros lugares públicos.

## Instalación

Si quieres probar o colaborar en el proyecto, sigue estos pasos:

### 1. Clona el repositorio:

```bash
git clone https://github.com/samu-tec/Friends4You.git
```

### 2. Accede a la carpeta del proyecto:

```bash
cd Friends4You
```

### 3. Instala las dependencias:

```bash
npm install
```

### 4. Inicia la aplicación:

```bash
ng serve
```

Luego, abre [http://localhost:4200](http://localhost:4200) en tu navegador para ver Friends4You en acción.

## Comandos Básicos de Angular

### Actualizar Angular CLI

```bash
ng update @angular/cli
```

### Iniciar el servidor de desarrollo

```bash
ng serve
```

### Crear un nuevo componente

```bash
ng generate component nombreComponente
```

### Crear un nuevo servicio

```bash
ng generate service nombreServicio
```

### Crear un nuevo módulo

```bash
ng generate module nombreModulo
```

### Crear una nueva ruta

```bash
ng generate module nombreModulo --routing
```

### Ejecutar pruebas unitarias

```bash
ng test
```

### Construir la aplicación para producción

```bash
ng build --prod
```

## Estructura del Proyecto

La estructura base actual del proyecto es la siguiente:

```
Friends4You/
├── src/
│   ├── app/
│   │   ├── app.component.css       # Estilos del componente principal
│   │   ├── app.component.html      # Plantilla del componente principal
│   │   ├── app.component.spec.ts   # Pruebas del componente principal
│   │   ├── app.component.ts        # Lógica del componente principal
│   │   ├── app.config.ts           # Configuraciones generales de la app
│   │   ├── app.routes.ts           # Configuración de rutas
│   ├── NombreComponente            # Estructura general de los otros componentes que iré añadiendo
│   │   ├── nombreComponente.component.html  # Plantilla del componente "NombreComponente"
│   │   ├── nombreComponente.component.css   # Estilos específicos del componente "NombreComponente"
│   │   ├── nombreComponente.component.ts    # Lógica específica del componente "NombreComponente"
│   ├── index.html              # Archivo HTML principal, donde se carga la aplicación Angular
│   ├── main.ts                 # Punto de entrada de la aplicación, arranca la app
│   ├── styles.css              # Estilos globales que se aplican a toda la aplicación
├── README.md                   # Documentación del proyecto
```

## Tecnologías Utilizadas

- [Angular](https://angular.io/)
- [TypeScript](https://www.typescriptlang.org/)
- [Node.js](https://nodejs.org/)
- [npm](https://www.npmjs.com/)

## Contribuciones

Este proyecto forma parte de mi proceso de aprendizaje y es de carácter privado, por lo que su uso, modificación o distribución requieren el permiso expreso de Samu_Tech. Sin embargo, si tienes sugerencias, mejoras o has identificado algún error, ¡estaré encantado de recibir tus aportaciones! Puedes enviar un pull request o abrir un issue en el repositorio; todas las contribuciones serán evaluadas cuidadosamente para ver si pueden integrarse al proyecto, siempre bajo los términos de la licencia propietaria.

## Licencia

Este proyecto es de **código propietario**. Se permite su uso, modificación y distribución solo con el permiso expreso de Samu_Tech. Cualquier distribución del código también requerirá autorización previa.

## Contacto

Si tienes preguntas o deseas más información, no dudes en contactarme a través de cualquiera de mis redes sociales: [https://linktr.ee/Samu_Tech](https://linktr.ee/Samu_Tech).

---

¡Gracias por tu interés en Friends4You! Espero que disfrutes explorando y colaborando en este proyecto.
