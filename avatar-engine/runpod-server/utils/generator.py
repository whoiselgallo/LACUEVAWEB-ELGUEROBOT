import base64
from diffusers import StableDiffusionXLImg2ImgPipeline, StableDiffusionXLPipeline
import torch
from io import BytesIO
from PIL import Image

device = "cuda"

# Cargar modelo
pipe = StableDiffusionXLPipeline.from_pretrained(
    "stabilityai/stable-diffusion-xl-base-1.0",
    torch_dtype=torch.float16
).to(device)

def generate_image(prompt, steps, guidance):
    image = pipe(
        prompt=prompt,
        num_inference_steps=steps,
        guidance_scale=guidance
    ).images[0]

    buffer = BytesIO()
    image.save(buffer, format="PNG")
    return base64.b64encode(buffer.getvalue()).decode()

def generate_image_with_reference(prompt, reference_b64, steps, guidance):
    ref_bytes = base64.b64decode(reference_b64)
    ref_image = Image.open(BytesIO(ref_bytes))

    image = pipe(
        prompt=prompt,
        image=ref_image,
        strength=0.7,
        num_inference_steps=steps,
        guidance_scale=guidance
    ).images[0]

    buffer = BytesIO()
    image.save(buffer, format="PNG")
    return base64.b64encode(buffer.getvalue()).decode()
