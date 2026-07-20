# 🐾 ACTUALIZACIÓN v2.0 - Huella Draggable y Minimizable

## ✨ Nuevas Características

### 1. **Huella Movible por la Pantalla** 🖱️
- Haz clic y arrastra la huella a cualquier lugar de la pantalla
- Se mantiene dentro de los límites de la ventana
- Cursor cambia a "grab" cuando está listo para arrastrar
- Cursor cambia a "grabbing" durante el arrastre

### 2. **Botón de Minimizar** −
- Ubicado en la esquina superior derecha de la huella
- Al clickear, convierte la huella en una miniatura de **120x140px**
- La miniatura es una **huella rellena en color cyan brillante**
- Efectos neon muy brillantes en la miniatura

### 3. **Miniatura Cyan Interactiva** ✨
Cuando la huella está minimizada:
- Se convierte en una pequeña huella de **cyan (#00ffff)**
- Tiene glow ultra brillante
- **Clickeable**: Al clickear la miniatura se restaura la huella completa
- Se puede arrastrar también cuando está minimizada
- Efecto hover: Escala 1.1x

### 4. **Cambios en la Interfaz**

#### Ocultos cuando se minimiza:
- Los 5 dedos (📤 💬 🎯 👤 ⭐)
- El área del chat
- Todo excepto la miniatura y el botón de minimizar

#### Visible cuando se minimiza:
- Botón de minimizar (convertido en botón de restaurar)
- Miniatura con forma de huella cyan brillante

---

## 🎮 Cómo Usar

### Arrastrar la Huella
```
1. Haz clic en cualquier parte de la huella (NO en los dedos ni en el chat)
2. Arrastra a donde quieras
3. Suelta el ratón
4. ¡Listo! La huella está en su nueva posición
```

### Minimizar
```
1. Haz clic en el botón "−" en la esquina superior derecha
2. La huella se convierte en una pequeña esfera cyan
3. ¡Puedes moverla en la pantalla!
```

### Restaurar
```
Opción 1: Clickea el botón "−" de la miniatura
Opción 2: Haz clic en cualquier parte de la miniatura cyan
3. ¡La huella vuelve a su tamaño normal!
```

---

## 🎨 Especificaciones Visuales

### Huella Completa
- Ancho: 500px (responsive: 90vw máximo)
- Altura: Auto (aspect-ratio 1/1.1)
- Contorno: Magenta pulsante 3px
- Animación: Neon pulsante magenta↔cyan (3s)
- Botón minimizar: 30x30px en esquina superior derecha

### Miniatura Minimizada
- Ancho: 120px
- Altura: 140px
- Relleno: Cyan brillante (#00ffff)
- Contorno: 2px cyan
- Glow: Intenso cyan (25px)
- Efecto hover: Escala 1.1x
- Botón minimizar: 25x25px

---

## 🔧 Propiedades Técnicas

### Estados
```javascript
pawAgentState = {
    isMinimized: false,      // ¿Está minimizado?
    isDragging: false,       // ¿Se está arrastrando?
    dragOffsetX: 0,          // Offset X del arrastre
    dragOffsetY: 0           // Offset Y del arrastre
}
```

### Funciones Nuevas
```javascript
setupDragAndDrop()     // Configura interactividad de arrastre
minimizePawAgent()     // Minimiza la huella
restorePawAgent()      // Restaura la huella
togglePawMinimize()    // Alterna entre minimizar/restaurar
```

### Eventos Capturados
- `mousedown` - Inicia arrastre (solo en paw-main)
- `mousemove` - Mueve la huella
- `mouseup` - Finaliza arrastre
- `click` en miniatura - Restaura

---

## 📱 Limitaciones del Arrastre

El arrastre **NO se activa** cuando clickeas en:
- ❌ Los 5 dedos (paw-toe)
- ❌ El botón de minimizar (paw-minimize-btn)
- ❌ El área del chat (paw-chat-area)
- ❌ Select/Input/Button dentro del chat
- ❌ Cuando la huella está minimizada (solo minimizar y restaurar)

---

## 🎯 Casos de Uso

### Caso 1: Usuario abre la página
```
1. Huella aparece en esquina inferior derecha
2. Usuario puede arrastrarla a donde quiera
3. O hacer clic en "−" para minimizar
```

### Caso 2: Usuario trabaja en otra parte
```
1. Minimiza la huella haciendo clic en "−"
2. Se convierte en pequeña esfera cyan brillante
3. Puede seguir viendo el chat pero ocupando menos espacio
```

### Caso 3: Usuario quiere seguir chateando
```
1. Clickea la miniatura cyan
2. Se restaura la huella al tamaño normal
3. Continúa escribiendo en el chat
```

---

## 🐛 Troubleshooting

| Problema | Solución |
|----------|----------|
| No puedo arrastrar | Intenta arrastrar desde el área gris de la huella, no desde los dedos |
| El botón − no funciona | Asegúrate de hacerle clic exactamente en el botón (esquina superior derecha) |
| La miniatura desapareció | Recarga la página o busca en las esquinas de la pantalla |
| Se queda pegada en la pantalla | Refresca con F5 o Ctrl+R |
| El drag es muy lento | Verifica la velocidad de tu conexión |

---

## 📊 Comparativa de Versiones

| Feature | v1.0 | v2.0 |
|---------|------|------|
| Huella neon | ✅ | ✅ |
| 5 dedos interactivos | ✅ | ✅ |
| Chat integrado | ✅ | ✅ |
| Draggable | ❌ | ✅ |
| Minimizable | ❌ | ✅ |
| Miniatura cyan | ❌ | ✅ |
| Botón minimizar | ❌ | ✅ |
| Responsive | ✅ | ✅ |

---

## 📁 Archivos Modificados

- ✅ `paw-agent.js` - Agregadas funciones de drag y minimizar
- ✅ `paw-agent.css` - Estilos para miniatura y drag
- ✅ `index.html` - Agregado botón de minimizar

---

## 🚀 Próximas Mejoras Sugeridas

- [ ] Guardar posición en localStorage
- [ ] Efecto de "bounce" al soltar el arrastre
- [ ] Snap a grid (alineación automática)
- [ ] Touch support para móviles
- [ ] Animación al minimizar (shrink)
- [ ] Sonido neon al minimizar

---

**Versión**: 2.0  
**Actualizado**: Mayo 2026  
**Estado**: ✅ Completo y funcional  

La huella de perro neon ahora es completamente móvil y con soporte de minimización. ¡Disfruta tu agente IA interactivo!
