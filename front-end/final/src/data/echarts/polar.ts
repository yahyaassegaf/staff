const data: [number, number][] = [];

for (let i = 0; i <= 360; i++) {
  const t = (i / 180) * Math.PI;
  const r = Math.sin(2 * t) * Math.cos(2 * t);
  data.push([r, i]);
}

export default function getData() {
  return {
    backgroundColor: "transparent",
    textStyle: {
      fontFamily: 'Inter, "Helvetica Neue", Arial, sans-serif',
      fontWeight: 300 as 300,
    },
    title: {
      text: "Dual Numeric Axis",
    },
    legend: {
      data: ["line"],
    },
    polar: {
      center: ["50%", "54%"] as [string, string],
    },
    tooltip: {
      trigger: "axis" as const,
      axisPointer: {
        type: "cross" as const,
      },
    },
    angleAxis: {
      type: "value" as const,
      startAngle: 0,
    },
    radiusAxis: {
      min: 0,
    },
    series: [
      {
        coordinateSystem: "polar" as const,
        name: "line",
        type: "line" as const,
        showSymbol: false,
        data: data,
      },
    ],
    animationDuration: 2000,
  };
}
