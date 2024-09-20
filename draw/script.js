document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    let currentImage = null;
    let images = [];
    let isDrawing = false; // To track if the user is currently drawing
    let isDrawingMode = false; // To track if drawing mode is enabled
    let drawStartX = 0;
    let drawStartY = 0;
    let drawings = []; // To store the lines drawn
    let totalLength = 0; // To store the total length of all lines drawn

    const pixelsPerInch = 96; // Example conversion factor, adjust based on canvas resolution

    class CanvasImage {
        constructor(image, x, y, width, height, opacity = 1) {
            this.image = image;
            this.x = x;
            this.y = y;
            this.width = width;
            this.height = height;
            this.flipX = false;
            this.flipY = false;
            this.selected = false;
            this.angle = 0; // Rotation angle
            this.opacity = opacity; // Opacity level
        }

        draw(ctx) {
            ctx.save();
            ctx.translate(this.x, this.y);
            ctx.rotate(this.angle * Math.PI / 180);
            ctx.scale(this.flipX ? -1 : 1, this.flipY ? -1 : 1);
            ctx.globalAlpha = this.opacity;
            ctx.drawImage(this.image, -this.width / 2, -this.height / 2, this.width, this.height);
            ctx.restore();

            if (this.selected) {
                ctx.globalAlpha = 1;
                ctx.strokeStyle = 'red';
                ctx.lineWidth = 2;
                ctx.strokeRect(this.x - this.width / 2, this.y - this.height / 2, this.width, this.height);
                ctx.fillStyle = 'blue';
                ctx.fillRect(this.x + this.width / 2 - 10, this.y - this.height / 2 - 10, 20, 20); // Resize handle
                ctx.fillStyle = 'green';
                ctx.fillRect(this.x + this.width / 2 - 10, this.y + this.height / 2 - 10, 20, 20); // Rotate handle
            }
        }
    }

    function drawAll() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        images.forEach(img => img.draw(ctx));
        drawings.forEach((drawPath, index) => {
            const start = drawPath[0];
            const end = drawPath[1];
            ctx.beginPath();
            ctx.moveTo(start.x, start.y);
            ctx.lineTo(end.x, end.y);
            ctx.stroke();

            // Display the length of the line next to it
            const lengthInInches = calculateLength(start, end) / pixelsPerInch;
            ctx.font = '14px Arial';
            ctx.fillStyle = 'black';
            ctx.fillText(`Line ${index + 1}: ${lengthInInches.toFixed(2)} inches`, (start.x + end.x) / 2, (start.y + end.y) / 2);
        });
    }

    function addImage(imageSrc) {
        let img = new Image();
        img.src = imageSrc;
        img.onload = () => {
            const canvasImage = new CanvasImage(img, canvas.width / 2, canvas.height / 2, 80, 15);
            images.push(canvasImage);
            currentImage = canvasImage;
            drawAll();
        };
        img.onerror = () => {
            console.error(`Failed to load image: ${imageSrc}`);
        };
    }

    document.getElementById('shape1').addEventListener('click', () => addImage('shapes/shape1.png'));
    document.getElementById('shape2').addEventListener('click', () => addImage('shapes/shape2.png'));
    document.getElementById('shape3').addEventListener('click', () => addImage('shapes/shape3.png'));
    document.getElementById('shape4').addEventListener('click', () => addImage('shapes/shape4.png'));
    document.getElementById('shape5').addEventListener('click', () => addImage('shapes/shape5.png'));

    document.getElementById('flip-button').addEventListener('click', () => {
        if (currentImage) {
            currentImage.flipX = !currentImage.flipX;
            drawAll();
        }
    });

    const saveButton = document.getElementById('save-button');
    saveButton.addEventListener('click', saveCanvas);

    function saveCanvas() {
        const dataURL = canvas.toDataURL('image/png');
        fetch('save_image.php', {
            method: 'POST',
            body: JSON.stringify({ image: dataURL }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Image saved successfully!');
            } else {
                alert('Failed to save image.');
            }
        });
    }

    const opacitySlider = document.getElementById('opacity-slider');
    opacitySlider.addEventListener('input', () => {
        if (currentImage) {
            currentImage.opacity = opacitySlider.value / 100;
            drawAll();
        }
    });

    document.getElementById('draw-button').addEventListener('click', () => {
        isDrawingMode = !isDrawingMode;
        toggleDrawButton();
        if (isDrawingMode) {
            canvas.style.cursor = 'crosshair';
        } else {
            canvas.style.cursor = 'default';
        }
    });

    // Toggles the appearance of the draw button when drawing is active or inactive
    function toggleDrawButton() {
        const drawButton = document.getElementById('draw-button');
        drawButton.textContent = isDrawingMode ? 'Drawing Mode: ON' : 'Drawing Mode: OFF';
    }

    // Handles starting drawing or selecting images
    canvas.addEventListener('mousedown', startAction);
    canvas.addEventListener('mousemove', performAction);
    canvas.addEventListener('mouseup', endAction);

    function startAction(e) {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        if (isDrawingMode) {
            isDrawing = true;
            drawStartX = x;
            drawStartY = y;
            ctx.beginPath();
            ctx.moveTo(drawStartX, drawStartY);
        }
    }

    function performAction(e) {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        if (isDrawing) {
            ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear canvas
            drawAll(); // Redraw everything
            ctx.lineTo(x, y); // Draw straight line to current mouse position
            ctx.stroke();
        }
    }

    function endAction(e) {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        if (isDrawing) {
            // Complete the line to the endpoint and stop drawing
            ctx.lineTo(x, y);
            ctx.stroke();
            drawings.push([{ x: drawStartX, y: drawStartY }, { x, y }]);

            // Calculate the length of the line
            const lengthInPixels = calculateLength({ x: drawStartX, y: drawStartY }, { x, y });
            const lengthInInches = lengthInPixels / pixelsPerInch;
            totalLength += lengthInInches;

            // Update the length display
            updateLengthDisplay(lengthInInches);
            updateTotalLengthDisplay();

            // Reset drawing states after mouseup
            isDrawing = false;
            isDrawingMode = false;
            toggleDrawButton(); // Update the button state
        }
    }

    function calculateLength(start, end) {
        return Math.sqrt(Math.pow(end.x - start.x, 2) + Math.pow(end.y - start.y, 2));
    }

    function updateLengthDisplay(lengthInInches) {
        const lineLengthBox = document.getElementById('line-length');
        lineLengthBox.value += `Line ${drawings.length}: ${lengthInInches.toFixed(2)} inches\n`;
    }

    function updateTotalLengthDisplay() {
        const totalLengthBox = document.getElementById('total-length');
        totalLengthBox.value = `Total Length: ${totalLength.toFixed(2)} inches`;
    }
});
