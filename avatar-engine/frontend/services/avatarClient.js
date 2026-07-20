import { api } from "./api";

export const AvatarClient = {
  createAvatar: (payload) => api.post("/avatars", payload),

  generateAction: (name, action) =>
    api.post(`/actions/${name}`, { action }),

  getAvatarProfile: (name) =>
    api.get(`/avatars/${name}`),

  getAllAvatars: () =>
    api.get("/avatars"),

  // 🧩 NUEVOS MÉTODOS PARA SETTINGS Y LOGS
  getSettings: () => api.get("/settings"),
  getLogs: () => api.get("/logs")
};
