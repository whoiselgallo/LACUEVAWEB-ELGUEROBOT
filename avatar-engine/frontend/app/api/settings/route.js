export async function GET() {
  return Response.json({
    theme: "neon",
    version: "1.0.0",
    author: "La Cueva del Güero",
  });
}
