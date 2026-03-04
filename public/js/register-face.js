// Initialize Face API logic
const elImage = document.querySelector('input[type="file"]');
const elDataFaceApi = document.getElementById('dataFaceApi');
const elForm = document.querySelector('form');
const submitBtn = elForm.querySelector('button[type="submit"]');

// Load models explicitly (SSD Mobilenet v1 is best for registration quality)
let modelsLoaded = false;
async function loadModels() {
    try {
        await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
        modelsLoaded = true;
        console.log("FaceAPI Models Loaded");
    } catch (err) {
        console.error("Error loading models:", err);
    }
}
// Start loading immediately
loadModels();

elForm.addEventListener('submit', async (e) => {
    // Check if user selected a file
    const file = elImage && elImage.files ? elImage.files[0] : null;
    
    // Check if we already processed the face data (prevent infinite loop)
    // If hidden input has value, it means we already successfully processed it.
    if (elDataFaceApi.value && file) {
        return; // Allow form to submit normally
    }

    // Only intercept if a file is selected
    if (file) {
        e.preventDefault();

        // UI Feedback
        const originalBtnText = submitBtn.innerText;
        submitBtn.disabled = true;
        submitBtn.innerText = "Processing Face Data...";

        try {
            // Wait for models if not ready
            if (!modelsLoaded) {
                submitBtn.innerText = "Loading AI Models...";
                await loadModels();
            }

            // 1. Convert file to image element
            const img = await faceapi.bufferToImage(file);
            
            // 2. Compute descriptor using SSD Mobilenet
            const detection = await faceapi.detectSingleFace(img)
                .withFaceLandmarks()
                .withFaceDescriptor();
            
            if (detection) {
                // 3. Save to hidden input
                elDataFaceApi.value = detection.descriptor.toString();
                // 4. Re-submit form. We use reportValidity() to mimic browser behavior or just submit()
                // Since we are inside a submit handler, calling elForm.submit() bypasses the handler
                elForm.submit(); 
            } else {
                alert("No face detected! Please use a clear picture.");
                submitBtn.disabled = false;
                submitBtn.innerText = originalBtnText;
            }
        } catch (err) {
            console.error(err);
            alert("Error processing face image. " + (err.message || err));
            submitBtn.disabled = false;
            submitBtn.innerText = originalBtnText;
        }
    }
    // If no file, let the form submit normally
});
