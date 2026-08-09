"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { AvatarClient } from "../../services/avatarClient";

export default function AvatarsPage() {
  const [avatars, setAvatars] = useState([]);

  useEffect(() => {
    AvatarClient.getAllAvatars().then((res) => {
      setAvatars(res?.avatars || []);
    });
  }, []);

  return (
    <main className="page">
      <h1 className="title-neon">Avatares Guardados</h1>

      <p className="subtitle-neon">
        Selecciona un avatar para ver sus actividades o generar nuevas.
      </p>

      <hr className="divider-neon" />

      <div className="avatars-grid">
        {avatars.length === 0 && (
          <p style={{ opacity: 0.7 }}>No hay avatares aún.</p>
        )}

        {avatars.map((avatar) => (
          <Link
            key={avatar.name}
            href={`/avatar/${avatar.name}`}
            className="avatar-card"
          >
            <img
              src={avatar.avatar_base_url}
              alt={avatar.name}
              className="avatar-thumb"
            />
            <p className="avatar-name">{avatar.name}</p>
          </Link>
        ))}
      </div>
    </main>
  );
}
