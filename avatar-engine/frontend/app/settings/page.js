"use client";
import { useEffect, useState } from "react";
import { AvatarClient } from "../../services/avatarClient";

export default function SettingsPage() {
  const [settings, setSettings] = useState(null);

  useEffect(() => {
    AvatarClient.getSettings().then(setSettings);
  }, []);

  return (
    <main className="page">
      <h1 className="title-neon">Configuración</h1>
      {settings ? (
        <pre>{JSON.stringify(settings, null, 2)}</pre>
      ) : (
        <p>Cargando configuración...</p>
      )}
    </main>
  );
}
