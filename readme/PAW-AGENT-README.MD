# 🐾 Agente Huella de Perro Neon - La Cueva del Güero

## 📋 Descripción

Se ha creado una **interfaz revolucionaria** para el agente IA con forma de **huella de perro con contornos neon pulsantes** entre **magenta (#ff00ff) y cyan (#00ffff)**.

El diseño integra los **5 dedos del perro como botones interactivos** y el **chat en la sección más amplia** de la huella.

---

## 🎯 Estructura Física

```
         📤    💬    🎯    👤    ⭐
      (Envío) (Chat) (Equipo) (Guest) (Seguidor)
         ↓     ↓      ↓      ↓      ↓
    ╔════════════════════════════════════╗
    ║                                    ║
    ║         ┌─────────────────┐        ║
    ║         │  CHAT PRINCIPAL │        ║
    ║         │  (85% x 72%)    │        ║
    ║         │                 │        ║
    ║         │  • Mensajes     │        ║
    ║         │  • Scroll       │        ║
    ║         │  • Input        │        ║
    ║         └─────────────────┘        ║
    ║                                    ║
    ║  HUELLA: Contorno Neon Pulsante    ║
    ║          Magenta ↔ Cyan (3s)       ║
    ║                                    ║
    ╚════════════════════════════════════╝
         (Dedo Grande del Perro)
                    ↓
                   ⭐
```

---

## 🎮 Funciones de los Dedos

| Dedo | Emoji | Función | Acción |
|------|-------|---------|--------|
| 1️⃣ | 📤 | Enviar Mensaje | Envía el texto del input al bot |
| 2️⃣ | 💬 | Toggle Chat | Muestra/Oculta el área de chat |
| 3️⃣ | 🎯 | Modo Equipo | Cambiar a modo EQUIPO (dorado) |
| 4️⃣ | 👤 | Modo Invitado | Cambiar a modo INVITADO (cyan) |
| 5️⃣ | ⭐ | Modo Seguidor | Cambiar a modo SEGUIDOR (magenta) |

---

## 🎨 Efectos Visuales

### Animaciones Principales

1. **Contorno de Huella (3s ciclo)**
   - Pulsante: Magenta → Cyan → Magenta
   - Glow effect: 10px → 20px → 10px
   - Permanente y suave

2. **Dedos (2.5s ciclo)**
   - Glow individual: Magenta → Cyan
   - Animación de hover: Escala 1.2x
   - Animación de clic: Escala con duración 0.3s

3. **Chat**
   - Slide-in: 0.4s al abrir
   - Mensajes con animación: 0.3s cada uno
   - Scroll suave automático

4. **Notificaciones**
   - Float desde arriba
   - Duración: 3s
   - Color neon magenta con glow cyan

---

## 💬 Sistema de Mensajes

### Estructura del Mensaje
```
┌─────────────────────────────────┐
│ [el-guero-bot]  [12:34]         │  ← Meta
├─────────────────────────────────┤
│ Tu mensaje aquí con estilo      │  ← Contenido
│ y respuestas en crudo...        │
└─────────────────────────────────┘
```

### Estilos
- **Usuario**: Fondo dorado, borde izquierdo naranja
- **Bot**: Fondo cyan, borde izquierdo cyan
- **Carga**: Fondo magenta, borde izquierdo magenta

---

## 🎛️ Panel de Control

```
┌─────────────────────────────────┐
│ 👤 INVITADO        ← Badge Tipo │
├─────────────────────────────────┤
│ [Selector de Tipo de Visita]   │
│  • 👤 Invitado  ← Default      │
│  • 🎯 Equipo                   │
│  • ⭐ Seguidor                 │
├─────────────────────────────────┤
│ [Input] [📤 Enviar]            │
│ "Tira línea aquí..."           │
└─────────────────────────────────┘
```

---

## 🔗 Colores Neon

| Elemento | Color | Código |
|----------|-------|--------|
| Magenta Neon | 🔴🔵 Magenta | `#ff00ff` |
| Cyan Neon | 🟦 Cyan | `#00ffff` |
| Dorado Equipo | 🟨 Amarillo | `#ffd700` |
| Fondo | Negro | `#000000` |

---

## 📱 Responsivo

- **Desktop**: 500px ancho, posición fija inferior-derecha
- **Tablet**: 95vw ancho, centrado
- **Mobile**: Ajusta tamaño de fuentes y dedos

---

## 🚀 Archivos Incluidos

### Nuevos Archivos
- `paw-agent.js` - Lógica del agente
- `paw-agent.css` - Estilos visuales
- `PAW-AGENT-README.md` - Documentación (este archivo)

### Archivos Modificados
- `index.html` - Inclusión de scripts y contenedor

---

## 🔧 Integración Técnica

### API Endpoint
```javascript
Recurso: /api-el-guero-bot.php
Método: POST
Body: {
  message: "Tu mensaje aquí",
  visitType: "guest|team|follower",
  estado: "chat"
}
Respuesta: { reply: "Respuesta del bot..." }
```

### Eventos Capturados
- Click en dedos → `handleToeClick()`
- Enter en input → `sendMessageFromPaw()`
- Change en selector → `updatePawVisitTypeUI()`

---

## 🎯 Casos de Uso

### 1. Visita de Invitado (Defecto)
```
Usuario: ¿Cuál es el tema del próximo episodio?
Bot: El tema será sobre horóscopos del mes...
```

### 2. Equipo
```
Usuario: Necesito un resumen ejecutivo
Bot: Claro, aquí te dejo los puntos clave...
```

### 3. Seguidor
```
Usuario: ¡Amo tu contenido!
Bot: ¡Apreciado! Síguenos para más...
```

---

## 🐛 Troubleshooting

| Problema | Solución |
|----------|----------|
| Dedos no clickean | Verificar CSS `pointer-events: all` |
| Chat no aparece | Revisar `#pawChatBody` en HTML |
| API no responde | Verificar ruta de `/api-el-guero-bot.php` |
| Colores sin glow | Navegador no soporta box-shadow |

---

## 📞 Contacto & Soporte

Para cambios o mejoras, revisar:
- `paw-agent.js` para lógica
- `paw-agent.css` para estilos
- `index.html` para estructura HTML

---

**Creado con 🐾 para La Cueva del Güero**
