/**
 * 🎬 LA CUEVA DEL GÜERO - FFMPEG WASM CLIENT-SIDE ENGINE
 * Ejecución de operaciones multimedia en el navegador usando WebAssembly
 * (Sin consumir minutos de CPU en Google Cloud para tareas ligeras).
 */

class FFmpegWasmHelper {
    constructor() {
        this.ffmpeg = null;
        this.isLoaded = false;
        this.isLoading = false;
    }

    async load() {
        if (this.isLoaded) return true;
        if (this.isLoading) {
            while (this.isLoading) {
                await new Promise(r => setTimeout(r, 100));
            }
            return this.isLoaded;
        }

        this.isLoading = true;
        try {
            if (typeof FFmpeg === "undefined") {
                console.warn("Librería FFmpeg WASM no detectada en window. Cargando dinámicamente...");
                await this.loadScript("https://unpkg.com/@ffmpeg/ffmpeg@0.12.10/dist/umd/ffmpeg.js");
                await this.loadScript("https://unpkg.com/@ffmpeg/util@0.12.1/dist/umd/index.js");
            }

            const { FFmpeg } = window.FFmpegWASM || window;
            this.ffmpeg = new FFmpeg();

            this.ffmpeg.on('log', ({ message }) => {
                console.log("[FFmpeg-WASM]", message);
            });

            const baseURL = 'https://unpkg.com/@ffmpeg/core@0.12.6/dist/umd';
            await this.ffmpeg.load({
                coreURL: `${baseURL}/ffmpeg-core.js`,
                wasmURL: `${baseURL}/ffmpeg-core.wasm`
            });

            this.isLoaded = true;
            this.isLoading = false;
            console.log("✓ Motor FFmpeg WASM inicializado en el navegador");
            return true;
        } catch (error) {
            console.error("Error al cargar FFmpeg WASM:", error);
            this.isLoading = false;
            return false;
        }
    }

    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement("script");
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Recorte rápido de clip (Quick Trim) en el cliente
     */
    async trimVideo(file, startTimeSec, durationSec) {
        await this.load();
        const { fetchFile } = window.FFmpegUtil || {};
        const inputName = 'input.mp4';
        const outputName = 'trimmed.mp4';

        const fileData = fetchFile ? await fetchFile(file) : new Uint8Array(await file.arrayBuffer());
        await this.ffmpeg.writeFile(inputName, fileData);

        // Recorte rápido copiando codecs
        await this.ffmpeg.exec([
            '-ss', startTimeSec.toString(),
            '-i', inputName,
            '-t', durationSec.toString(),
            '-c', 'copy',
            outputName
        ]);

        const data = await this.ffmpeg.readFile(outputName);
        return new Blob([data.buffer], { type: 'video/mp4' });
    }

    /**
     * Extraer audio local en MP3 para enviar a Gemini sin subir el video de gigabytes
     */
    async extractAudioForGemini(file) {
        await this.load();
        const { fetchFile } = window.FFmpegUtil || {};
        const inputName = 'video_in.mp4';
        const outputName = 'audio_gemini.mp3';

        const fileData = fetchFile ? await fetchFile(file) : new Uint8Array(await file.arrayBuffer());
        await this.ffmpeg.writeFile(inputName, fileData);

        await this.ffmpeg.exec([
            '-i', inputName,
            '-vn',
            '-ac', '1',
            '-ar', '16000',
            '-b:a', '64k',
            outputName
        ]);

        const data = await this.ffmpeg.readFile(outputName);
        return new Blob([data.buffer], { type: 'audio/mp3' });
    }
}

window.cuevaFFmpeg = new FFmpegWasmHelper();
