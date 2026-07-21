/**
 * 🎭 LA CUEVA DEL GÜERO - AVATAR ENGINE & PROPS GENERATOR
 * File: /js/avatar-engine.js
 */

const API_AVATAR_URL = "/api/api-avatar-engine.php";

document.addEventListener("DOMContentLoaded", () => {
    cargarAvataresRegistrados();
});

async function cargarAvataresRegistrados() {
    try {
        const response = await fetch(`${API_AVATAR_URL}?action=list`);
        const data = await response.json();
        const select = document.getElementById("avatarCharacterSelect");
        const gallery = document.getElementById("avatarGallery");

        if (!data.success || !data.avatars) return;

        if (select) {
            select.innerHTML = '<option value="">-- Selecciona un personaje --</option>';
            data.avatars.forEach((av) => {
                const opt = document.createElement("option");
                opt.value = av.nombre;
                opt.textContent = `${av.nombre} ${av.episodio ? '(' + av.episodio + ')' : ''}`;
                select.appendChild(opt);
            });
        }

        if (gallery) {
            if (data.avatars.length === 0) {
                gallery.innerHTML = '<p style="color:#aaa; grid-column: 1/-1; text-align:center;">No hay avatares registrados aún.</p>';
                return;
            }

            let html = "";
            data.avatars.forEach((av) => {
                html += `
                    <div style="background:rgba(0,0,0,0.5); border:1px solid var(--neon-cyan); border-radius:12px; padding:15px; text-align:center;">
                        <h4 style="color:#00FFFF; margin:0 0 5px;">${av.nombre}</h4>
                        <span style="font-size:0.75rem; color:#FF00FF; border:1px solid #FF00FF; padding:2px 6px; border-radius:10px;">${av.episodio || 'Personaje Base'}</span>
                        <p style="font-size:0.8rem; color:#aaa; margin:8px 0 0;">${av.rasgos_faciales || 'Rasgos registrados'}</p>
                    </div>
                `;
            });
            gallery.innerHTML = html;
        }
    } catch (e) {
        console.error("Error cargando avatares:", e);
    }
}

async function registrarNuevoAvatar(e) {
    if (e) e.preventDefault();

    const nombre = document.getElementById("avatar-nombre")?.value;
    const rasgos = document.getElementById("avatar-rasgos")?.value;
    const pdfInput = document.getElementById("avatar-pdf")?.files[0];
    const frenteInput = document.getElementById("avatar-frente")?.files[0];

    if (!nombre) {
        alert("El nombre del personaje es obligatorio.");
        return;
    }

    if (!pdfInput) {
        alert("Es OBLIGATORIO adjuntar el Documento Firmado de Consentimiento de Uso de Imagen.");
        return;
    }

    // Convertir PDF a DataURL
    const pdfReader = new FileReader();
    pdfReader.onload = async (event) => {
        const consentPdfData = event.target.result;

        const payload = {
            action: "create",
            nombre: nombre,
            rasgos_faciales: rasgos,
            consentimiento_pdf: consentPdfData,
            foto_frente: "registrado",
            foto_perfil_izq: "registrado",
            foto_perfil_der: "registrado"
        };

        try {
            const resp = await fetch(API_AVATAR_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            const resData = await resp.json();
            if (resData.success) {
                alert(resData.message);
                cargarAvataresRegistrados();
            } else {
                alert("Error: " + resData.error);
            }
        } catch (err) {
            alert("Error registrando avatar: " + err.message);
        }
    };
    pdfReader.readAsDataURL(pdfInput);
}

// MODAL OCULTO: IMPORTAR AVATARES PRE-EXISTENTES
function abrirModalImportarExistente() {
    const modal = document.getElementById("modalImportarAvatarExistente");
    if (modal) modal.style.display = "flex";
}

function cerrarModalImportarExistente() {
    const modal = document.getElementById("modalImportarAvatarExistente");
    if (modal) modal.style.display = "none";
}

async function guardarAvatarPreExistente(e) {
    if (e) e.preventDefault();

    const nombre = document.getElementById("import-nombre")?.value;
    const episodio = document.getElementById("import-episodio")?.value;
    const imgInput = document.getElementById("import-imagen")?.files[0];
    const pdfInput = document.getElementById("import-pdf")?.files[0];

    if (!nombre || !imgInput) {
        alert("Nombre e Imagen limpia sin fondo son obligatorios.");
        return;
    }

    if (!pdfInput) {
        alert("Es OBLIGATORIO adjuntar el Documento Firmado de Consentimiento de Uso de Imagen.");
        return;
    }

    const imgReader = new FileReader();
    imgReader.onload = (eImg) => {
        const imgData = eImg.target.result;

        const pdfReader = new FileReader();
        pdfReader.onload = async (ePdf) => {
            const pdfData = ePdf.target.result;

            const payload = {
                action: "import",
                nombre: nombre,
                episodio: episodio,
                imagen_limpia: imgData,
                consentimiento_pdf: pdfData
            };

            try {
                const resp = await fetch(API_AVATAR_URL, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                const resData = await resp.json();
                if (resData.success) {
                    alert(resData.message);
                    cerrarModalImportarExistente();
                    cargarAvataresRegistrados();
                } else {
                    alert("Error: " + resData.error);
                }
            } catch (err) {
                alert("Error importando avatar: " + err.message);
            }
        };
        pdfReader.readAsDataURL(pdfInput);
    };
    imgReader.readAsDataURL(imgInput);
}

async function generarHumanoideAislado() {
    const nombre = document.getElementById("avatarCharacterSelect")?.value;
    const actividad = document.getElementById("avatarActividadSelect")?.value;
    const ropa = document.getElementById("avatarRopaSelect")?.value;

    if (!nombre) {
        alert("Selecciona un personaje registrado.");
        return;
    }

    try {
        const resp = await fetch(API_AVATAR_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "generate", nombre, actividad, ropa })
        });
        const data = await resp.json();

        if (data.success) {
            const output = document.getElementById("avatarResultOutput");
            if (output) {
                output.innerHTML = `
                    <div style="background:rgba(0,255,255,0.05); border:1px solid var(--neon-cyan); border-radius:12px; padding:20px; text-align:left;">
                        <h3 style="color:#00FFFF; margin:0 0 10px;">🎭 Humanoide Aislado Generado (Estilo Comic Neón)</h3>
                        <p><strong>Personaje:</strong> ${data.character}</p>
                        <p><strong>Pose/Actividad:</strong> ${data.actividad}</p>
                        <p><strong>Vestimenta:</strong> ${data.ropa}</p>
                        <p><strong>Fondo:</strong> <span style="color:#39FF14;">Transparente PNG (Sin objetos de fondo)</span></p>
                        <div style="margin-top:15px; padding:10px; background:#000; border-radius:8px;">
                            <small style="color:#888;">Prompt técnico de generación:</small>
                            <p style="font-size:0.85rem; color:#FF00FF; margin:5px 0 0;">${data.prompt}</p>
                        </div>
                    </div>
                `;
            }
        } else {
            alert("Error: " + data.error);
        }
    } catch (e) {
        alert("Error generando avatar: " + e.message);
    }
}

function generarPropObjeto() {
    const objeto = document.getElementById("propSelect")?.value || "Sofá Neón";
    const output = document.getElementById("propResultOutput");
    if (output) {
        output.innerHTML = `
            <div style="background:rgba(255,0,255,0.05); border:1px solid var(--neon-magenta); border-radius:12px; padding:15px; text-align:left;">
                <h4 style="color:#FF00FF; margin:0 0 5px;">🛋️ Objeto/Prop Generado (Estilo Comic Neón)</h4>
                <p style="margin:0; font-size:0.9rem; color:#fff;">Objeto: <strong>${objeto}</strong> | Fondo Transparente PNG</p>
                <small style="color:#aaa;">Listo para superponer sobre el avatar en el Editor Canva PRO.</small>
            </div>
        `;
    }
}
