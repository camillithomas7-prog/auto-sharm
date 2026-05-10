#!/bin/bash
cd "$(dirname "$0")"

run() {
  local file="$1" prompt="$2"
  if [ -f "../assets/$file" ] && [ "$(stat -f%z "../assets/$file" 2>/dev/null)" -gt 100000 ]; then
    echo "✓ $file già presente, skip"
    return 0
  fi
  echo "▶ generating $file ..."
  php gen-one-car.php "$file" "$prompt"
}

run "car-corolla.jpg" "Professional editorial photo of a silver Toyota Corolla sedan, latest generation, parked at a luxury hotel entrance in Sharm El Sheikh, palm trees, blue sky, beautiful warm sunlight, three-quarter front angle, ultra-sharp focus, clean reflections on the body, photorealistic, no people, no text, premium automotive photography."

run "car-wrangler.jpg" "Professional editorial photo of a sand-beige Jeep Wrangler 4x4 with removable top, parked on a sandy desert road in the Sinai mountains near Sharm El Sheikh at golden hour, dramatic warm light, dust softly lit, three-quarter front low angle, photorealistic, adventurous mood, no people, no visible text or logos, premium automotive photography."

run "car-eclass.jpg" "Professional editorial photo of a black Mercedes-Benz E-Class sedan, latest model, parked in front of a modern luxury resort entrance in Sharm El Sheikh, marble floor, warm soft evening light, glossy reflections, three-quarter front view, ultra-sharp, photorealistic, premium executive look, no people, no visible text or logos."

run "car-h1.jpg" "Professional editorial photo of a white Hyundai H1 9-seater minivan, latest generation, parked at the Sharm El Sheikh airport pickup area at sunrise, palm trees, soft warm light, three-quarter front angle, ultra-sharp, photorealistic, clean and modern, no people, no visible text or logos, premium automotive photography."

echo "=== ALL DONE ==="
ls -la ../assets/*.jpg
