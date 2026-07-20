"use client";
import { useState } from "react";
import { AvatarClient } from "../../../services/avatarClient";
import Loader from "../../../components/Loader";

export default function CreateAvatarPage() {
  const [name, setName] = useState("");
  const [loading, setLoading] = useState(false);

  const handleCreate = async () => {
    setLoading(true);
    await AvatarClient.createAvatar(name);
    setLoading(false);
  };

  return (
    <main className="page">
      <h1 className="title-neon">Crear nuevo avatar</h1>
      <input placeholder="Nombre del avatar" value={name} onChange={e => setName(e.target.value)} />
      <button className="btn-neon-cyan" onClick={handleCreate}>Crear</button>
      {loading && <Loader label="Creando avatar..." />}
    </main>
  );
}
