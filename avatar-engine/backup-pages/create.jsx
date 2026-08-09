import { useState } from "react";
import UploadBox from "../components/UploadBox";
import AvatarPreview from "../components/AvatarPreview";
import Loader from "../components/Loader";
import { fileToBase64 } from "../utils/fileToBase64";
import { AvatarClient } from "../services/avatarClient";

export default function CreateAvatarPage() {
  const [imageB64, setImageB64] = useState(null);
  const [name, setName] = useState("");
  const [avatarURL, setAvatarURL] = useState(null);
  const [loading, setLoading] = useState(false);

  const handleFile = async (file) => {
    const b64 = await fileToBase64(file);
    setImageB64(b64);
  };

  const handleCreate = async () => {
    if (!imageB64 || !name) return;

    setLoading(true);

    const res = await AvatarClient.createAvatar({
      image: imageB64,
      name,
      traits: {}
    });

    setAvatarURL(res?.profile?.avatar_base_url || null);
    setLoading(false);
  };

  return (
    <main className="page">
      <h2>Crear avatar</h2>

      <input
        placeholder="Nombre del avatar (ej. Junior)"
        value={name}
        onChange={(e) => setName(e.target.value)}
      />

      <UploadBox onFile={handleFile} />

      {imageB64 && (
        <AvatarPreview
          src={`data:image/png;base64,${imageB64}`}
          label="Foto original"
        />
      )}

      <button onClick={handleCreate} disabled={loading || !imageB64 || !name}>
        Generar avatar
      </button>

      {loading && <Loader text="Generando avatar..." />}

      {avatarURL && <AvatarPreview src={avatarURL} label="Avatar generado" />}
    </main>
  );
}
