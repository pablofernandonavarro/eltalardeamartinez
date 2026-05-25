#!/usr/bin/env python3
"""
Extrae datos del PDF de liquidación de expensas iData/MisExpensas.

Uso: python scripts/extract_liquidacion.py /path/to/liquidacion.pdf
Salida: JSON a stdout (solo el JSON, sin texto extra).
"""

import sys
import json
import re

try:
    import pdfplumber
except ImportError:
    print(json.dumps({"error": "pdfplumber no instalado. Ejecutar: pip install pdfplumber"}))
    sys.exit(1)


MONTH_MAP = {
    "ENERO": 1, "FEBRERO": 2, "MARZO": 3, "ABRIL": 4,
    "MAYO": 5, "JUNIO": 6, "JULIO": 7, "AGOSTO": 8,
    "SEPTIEMBRE": 9, "OCTUBRE": 10, "NOVIEMBRE": 11, "DICIEMBRE": 12,
}

RUBRO_NAMES = {
    1: "REMUNERACIONES AL PERSONAL",
    2: "SERVICIOS PUBLICOS",
    3: "ABONOS",
    4: "MANTENIMIENTO",
    5: "REPARACIONES",
    6: "GASTOS BANCARIOS",
    7: "LIMPIEZA",
    8: "ADMINISTRACION",
    9: "SEGUROS",
    10: "OTROS",
}


def parse_amount(raw: str) -> float:
    """Convierte monto argentino a float. '621.019,61' -> 621019.61"""
    s = raw.strip().replace(".", "").replace(",", ".")
    try:
        return float(s)
    except ValueError:
        return 0.0


def clean_line(line: str) -> str:
    """Limpia artefactos comunes del PDF."""
    # Quitar artefactos de encoding como (cid:2), (cid:10), etc.
    line = re.sub(r'\(cid:\d+\)', '', line)
    # Quitar el prefijo de iData
    line = re.sub(r'Procesado\s+por\s+sistemas\s+de\s+WWW\.IDATA\.AR\s*', '', line)
    return line


def extract_text_from_pdf(pdf_path: str) -> str:
    """
    Extrae texto del PDF preservando el layout de columnas.
    Usa layout=True para que pdfplumber mantenga los espacios entre columnas.
    """
    full_text = ""
    with pdfplumber.open(pdf_path) as pdf:
        for page in pdf.pages:
            # layout=True preserva el espaciado de columnas (equivalente a liquidacion_texto.txt)
            try:
                page_text = page.extract_text(layout=True)
            except TypeError:
                # Versiones antiguas de pdfplumber no tienen layout=
                page_text = page.extract_text(x_tolerance=2, y_tolerance=2)

            if page_text:
                full_text += page_text + "\n"

    return full_text


def extract_period(text: str) -> dict:
    """Extrae período del encabezado."""
    m = re.search(r'Liquidaci[oó]n\s+de\s+mes\s+([A-ZÁÉÍÓÚ]+)/(\d{4})', text, re.IGNORECASE)
    if not m:
        m = re.search(r'Periodo[:\s]+([A-ZÁÉÍÓÚ]+)/(\d{4})', text, re.IGNORECASE)
    if not m:
        return {"period": "", "month": 0, "year": 0}

    month_name = m.group(1).upper()
    year = int(m.group(2))
    month = MONTH_MAP.get(month_name, 0)
    return {"period": f"{month_name}/{year}", "month": month, "year": year}


def extract_rubros(text: str) -> list:
    """
    Extrae los totales de rubros 1-10.
    Patrón: "TOTAL RUBRO N   $ 3.532.522,29" (con posibles espacios y $)
    """
    rubros = []
    for num in range(1, 11):
        # Busca "TOTAL RUBRO N" seguido de monto con formato argentino
        pattern = rf'TOTAL\s+RUBRO\s+{num}\s+\$?\s*([\d.]+,\d{{2}})'
        m = re.search(pattern, text, re.IGNORECASE)
        if m:
            amount = parse_amount(m.group(1))
            rubros.append({
                "number": num,
                "name": RUBRO_NAMES.get(num, f"RUBRO {num}"),
                "total": amount,
            })
    return rubros


def extract_total_gastos(text: str) -> float:
    """Extrae el total general de gastos."""
    # Busca "TOTAL DE GASTOS  100%" seguido del monto
    m = re.search(r'TOTAL\s+DE\s+GASTOS\s+100%\s+([\d.]+,\d{2})', text, re.IGNORECASE)
    if m:
        return parse_amount(m.group(1))
    return 0.0


def get_building_from_depto(depto: str) -> str:
    """Determina la torre a partir del número de departamento."""
    digits = re.sub(r'\D', '', depto)
    if len(digits) < 3:
        return ""
    # Los últimos 2 dígitos son piso/unidad dentro de la torre
    tower_num = int(digits[:-2])
    if tower_num <= 0:
        return ""
    return f"Torre {tower_num}"


def parse_unit_line(line: str) -> dict | None:
    """
    Parsea una línea de la tabla de UFs.

    Con layout=True el formato tiene columnas bien separadas:
    0002    704    RUGGIERI JUAN P...    485138,01   -388110,01  ...  0,26%  621019,61  ...  487683,01

    La estrategia:
    1. Detectar UF (4 dígitos) + depto (2-4 dígitos)
    2. Encontrar el porcentaje (N,NN%) para separar propietario de montos
    3. Extraer GASTOS A: primer monto positivo DESPUÉS del porcentaje
    4. El propietario es todo lo que está entre el depto y el primer monto grande
    """
    line = clean_line(line)

    # Debe comenzar con 4 dígitos
    if not re.match(r'^\d{4}\s', line.strip()):
        return None

    line = line.strip()

    # Capturar UF y depto
    m = re.match(r'^(\d{4})\s+(\d{2,4})\s+(.+)', line)
    if not m:
        return None

    uf = m.group(1)
    depto = m.group(2)
    rest = m.group(3)

    # Validar rango de UF
    if int(uf) < 1:
        return None

    building = get_building_from_depto(depto)
    if not building:
        return None

    # Extraer coeficiente (porcentaje)
    coeff = 0.0
    m_pct = re.search(r'(\d{1,2},\d{2})%', line)
    if m_pct:
        pct_str = m_pct.group(1).replace(',', '.')
        coeff = round(float(pct_str) / 100, 4)

    # El propietario está antes del primer monto numérico grande en `rest`
    # Estrategia: buscar el punto donde aparece el primer monto (secuencia de dígitos,dígitos)
    # Los montos pueden estar muy pegados si el layout no es perfecto
    owner = ""
    m_owner = re.match(r'^([^0-9\-]*[A-ZÁÉÍÓÚÑ][^0-9\-]*?)\s{2,}[-\d]', rest)
    if m_owner:
        owner = m_owner.group(1).strip()
    else:
        # Fallback: tomar palabras hasta el primer número con coma
        parts = rest.split()
        owner_parts = []
        for part in parts:
            if re.match(r'^-?[\d.]+,\d{2}$', part):
                break
            if re.match(r'^\d{1,2},\d{2}%$', part):
                break
            owner_parts.append(part)
        owner = ' '.join(owner_parts).strip()

    # Limpiar owner: quitar puntos finales/elipsis de truncado
    owner = re.sub(r'\.{2,}$', '', owner).rstrip('.').strip()

    if not owner:
        return None

    # Extraer GASTOS A: primer monto positivo después del porcentaje
    gastos_a = 0.0
    if m_pct:
        after_pct = line[m_pct.end():]
        m_ga = re.search(r'([\d.]+,\d{2})', after_pct)
        if m_ga:
            gastos_a = parse_amount(m_ga.group(1))

    # S.ANTERIOR: primer monto positivo de la línea (antes del porcentaje)
    all_amounts_str = re.findall(r'-?[\d.]+,\d{2}', line)
    amounts = []
    for a in all_amounts_str:
        try:
            amounts.append(parse_amount(a))
        except Exception:
            pass

    s_anterior = next((a for a in amounts if a > 100), 0.0)
    pagos = next((a for a in amounts if a < -100), 0.0)
    total = amounts[-1] if amounts else 0.0

    return {
        "uf": uf,
        "depto": depto,
        "owner": owner,
        "coefficient": coeff,
        "gastos_a": gastos_a,
        "s_anterior": s_anterior,
        "pagos": pagos,
        "bonific": 0.0,
        "deuda": 0.0,
        "intereses": 0.0,
        "total": total,
        "building": building,
    }


def extract_unit_rows(text: str) -> list:
    """Parsea todas las filas de la tabla de UFs."""
    units = []
    seen_ufs = set()

    for line in text.splitlines():
        line_clean = clean_line(line).strip()
        if not line_clean:
            continue

        if not re.match(r'^\d{4}\s', line_clean):
            continue

        unit = parse_unit_line(line_clean)
        if unit is None:
            continue

        if unit["uf"] in seen_ufs:
            continue

        seen_ufs.add(unit["uf"])
        units.append(unit)

    return units


def extract_from_pdf(pdf_path: str) -> dict:
    full_text = extract_text_from_pdf(pdf_path)

    period_info = extract_period(full_text)
    rubros = extract_rubros(full_text)
    total_gastos = extract_total_gastos(full_text)
    units = extract_unit_rows(full_text)

    return {
        "period": period_info["period"],
        "period_year": period_info["year"],
        "period_month": period_info["month"],
        "total_gastos": total_gastos,
        "rubros": rubros,
        "units": units,
    }


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Uso: python extract_liquidacion.py /path/to/file.pdf"}))
        sys.exit(1)

    pdf_path = sys.argv[1]

    try:
        result = extract_from_pdf(pdf_path)
        print(json.dumps(result, ensure_ascii=False))
    except FileNotFoundError:
        print(json.dumps({"error": f"Archivo no encontrado: {pdf_path}"}))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)
