let points = populatePoints(rawPoints);
let canvas = document.getElementById("canvas");
let ctx = canvas.getContext("2d");
ctx.fillStyle = "#000000";
animate();

async function animate() {
    for (let i = 0; i < 100000; i++) {
        if (i > 10070 && i < 10078) {
            drawPoints(points);
            await sleep(500);
        }
        movePoints(points);

    }
}


function populatePoints(rawPoints)
{
    let points = [];

    rawPoints.forEach((point, index) => {
        points.push(new Point(point.x, point.y, point.dx, point.dy));
    });

    return points;
}

function movePoints(points)
{
    points.forEach((point, index) => {
        point.x = point.x + point.dx;
        point.y = point.y + point.dy;
    });
}

function drawPoints(points)
{
    // Start by finding the extents
    let minX = 0;
    let minY = 0;
    let maxX = 0;
    let maxY = 0;

    points.forEach((point, index) => {
        if (point.x < minX) minX = point.x;
        if (point.x > maxX) maxX = point.x;
        if (point.y < minY) minY = point.y;
        if (point.y > maxY) maxY = point.y;
    });

    let width = maxX - minX;
    let height = maxY - minY;

    let scaleX = canvas.width / width;
    let scaleY = canvas.height / height;

    // Now plot the points on the canvas, with scaling
    // clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    points.forEach((point, index) => {
        let x = ((point.x - minX) * scaleX) - 100;
        let y = ((point.y - minY) * scaleY) - 100;

        ctx.fillRect(x, y, 2, 2);
    });
}

function Point(x, y, dx, dy) {
    this.x = parseInt(x);
    this.y = parseInt(y);
    this.dx = parseInt(dx);
    this.dy = parseInt(dy);
}

function sleep(ms)
{
    return(
        new Promise(function(resolve, reject)
        {
            setTimeout(function() { resolve(); }, ms);
        })
    );
}