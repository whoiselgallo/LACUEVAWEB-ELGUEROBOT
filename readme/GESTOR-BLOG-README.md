# 📖 Gestor Automático de Blog - La Cueva del Güero

## 🚀 Inicio Rápido

### **Paso 1: Acceder al Gestor**
Abre en tu navegador:
```
http://localhost/La Cueva WEB/manage-blog.html
```

---

## 📋 Proceso para Publicar desde PDF

### **TAB 1️⃣ - SUBIR PDF**

1. **Selecciona tu PDF:**
   - Haz clic en el área de carga o arrastra un PDF
   - El sistema validará que sea un PDF y no exceda 10MB

2. **Procesa el PDF:**
   - Haz clic en "🔍 Procesar PDF"
   - El contenido se extrae automáticamente
   - Verás una vista previa del texto extraído

3. **Resultado:**
   - El contenido se copiará automáticamente al formulario de edición
   - Se abrirá la pestaña "Editar Post"

---

### **TAB 2️⃣ - EDITAR POST**

Aquí editarás y publicarás el post:

| Campo | Requerido | Descripción |
|-------|-----------|------------|
| **Título** | ✅ Sí | El nombre del post |
| **Autor** | ✅ Sí | Quién escribió (ej: El Güero) |
| **Fecha** | ❌ No | Fecha de publicación (por defecto hoy) |
| **Categoría** | ❌ No | Tipo de contenido |
| **Contenido** | ✅ Sí | El texto completo del post |
| **Extracto** | ❌ No | Resumen breve (se genera automático si lo dejas vacío) |

**Acciones:**
- **👁️ Previsualizar** - Ver cómo se verá el post
- **✅ Publicar Post** - Guardar y publicar
- **Limpiar Formulario** - Borrar todo

---

### **TAB 3️⃣ - HISTORIAL**

- **🔄 Cargar Posts Publicados** - Muestra todos los posts guardados
- **📋 Copiar Código** - Copia el código del post para agregar a blog.js

---

## ⚡ Flujo Completo

```
1. Subir PDF
   ↓
2. El sistema extrae el texto
   ↓
3. Se abre automáticamente "Editar Post"
   ↓
4. Revisa y edita el contenido
   ↓
5. Haz clic en "✅ Publicar Post"
   ↓
6. ¡Listo! Recibirás el código para agregar a blog.js
```

---

## 📝 Después de Publicar

Cada post publicado genera un código como este:

```javascript
{
    id: 1714354123,
    titulo: "La Mente es el Arma Más Poderosa",
    fecha: "2026-04-28",
    autor: "El Güero",
    categoria: "reflexion",
    contenido: `Texto completo aquí...`,
    excerpt: "Resumen breve del contenido..."
}
```

### **Agregar el Post a blog.js:**

1. Abre [blog.js](blog.js)
2. Busca el array `blogPosts = [`
3. Copia el código generado
4. Pégalo dentro del array (antes del cierre `]`)
5. ¡Guarda el archivo!

**Ejemplo:**
```javascript
const blogPosts = [
    // Posts existentes...
    {
        id: 5,
        titulo: "Mi Nuevo Post",
        // ... resto del objeto
    }
];
```

---

## 🛠️ Características Técnicas

### **Backend (upload-blog.php)**
- ✅ Valida archivos PDF
- ✅ Límite de 10MB por archivo
- ✅ Guarda en carpeta `uploads/pdfs/`
- ✅ Almacena posts en `posts.json`
- ✅ Soporte para pdftotext (si está instalado)

### **Frontend (manage-blog.js)**
- ✅ Extrae texto de PDFs usando PDF.js
- ✅ Interfaz intuitiva con drag & drop
- ✅ Vista previa en tiempo real
- ✅ Validación de formularios
- ✅ Copiar código al portapapeles

### **Estilos (manage-blog.html)**
- ✅ Tema oscuro como La Cueva
- ✅ Colores: Verde (#4EFC22) y Cian (#00ffff)
- ✅ Responsive design
- ✅ Animaciones suaves

---

## 🔧 Configuración

### **Cambiar Categorías**
Si quieres agregar más categorías, edita el `<select>` en `manage-blog.html`:

```html
<select id="post-categoria">
    <option value="reflexion">Reflexión</option>
    <option value="articulo reflexivo">Artículo Reflexivo</option>
    <option value="horoscopo">Horóscopo</option>
    <!-- Agrega aquí más opciones -->
</select>
```

### **Cambiar Límite de Tamaño**
En `upload-blog.php`, busca:
```php
if ($archivo['size'] > 10 * 1024 * 1024) {
    // Cambia 10 a otro número (en MB)
}
```

---

## 🚨 Solución de Problemas

### **"Error al subir el archivo"**
- Verifica que sea un PDF válido
- Asegúrate que no exceda 10MB
- Intenta con otro navegador

### **"El PDF no se procesa"**
- El archivo PDF podría estar en formato especial
- Intenta convertirlo a PDF estándar

### **"Error al guardar el post"**
- Verifica que la carpeta `uploads/` exista
- Asegúrate de tener permisos de escritura
- Verifica que PHP está habilitado en tu servidor

### **No aparecen posts en "Historial"**
- Presiona "🔄 Cargar Posts Publicados" nuevamente
- Verifica que `posts.json` existe en la raíz

---

## 📚 Archivos Creados

```
La Cueva WEB/
├── manage-blog.html      ← Interfaz del gestor
├── manage-blog.js        ← Lógica del cliente
├── upload-blog.php       ← Procesamiento en servidor
├── posts.json            ← Archivo de posts (se crea automáticamente)
└── uploads/
    └── pdfs/             ← Carpeta para PDFs (se crea automáticamente)
```

---

## 🎯 Próximas Mejoras Opcionales

- [ ] Editar posts existentes
- [ ] Eliminar posts
- [ ] Agregar imágenes destacadas
- [ ] Validar contra duplicados
- [ ] Exportar posts a diferentes formatos
- [ ] Sistema de versiones/historial

---

## ✅ ¡Listo para Usar!

Abre **manage-blog.html** y comienza a publicar posts desde tus PDFs.

**¡Bienvenido a La Cueva del Güero! 🎙️**
