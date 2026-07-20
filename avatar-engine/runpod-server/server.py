from fastapi import FastAPI, UploadFile
from pydantic import BaseModel
from utils.generator import generate_image, generate_image_with_reference

app = FastAPI()

class GenerateRequest(BaseModel):
    prompt: str
    steps: int = 30
    guidance: float = 7.5

class ActionRequest(BaseModel):
    prompt: str
    reference: str  # base64 del avatar base
    steps: int = 30
    guidance: float = 7.5

@app.post("/generate")
async def generate(req: GenerateRequest):
    result = generate_image(req.prompt, req.steps, req.guidance)
    return {"image": result}

@app.post("/generate-action")
async def generate_action(req: ActionRequest):
    result = generate_image_with_reference(
        req.prompt,
        req.reference,
        req.steps,
        req.guidance
    )
    return {"image": result}
