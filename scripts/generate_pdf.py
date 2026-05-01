import sys
import json
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.units import cm
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer, HRFlowable
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT
from reportlab.pdfgen import canvas as pdfcanvas

json_path = sys.argv[1]
pdf_path  = sys.argv[2]

with open(json_path, 'r', encoding='utf-8') as f:
    data = json.load(f)

# Couleurs BTL
GOLD   = colors.HexColor('#c9a84c')
DARK   = colors.HexColor('#0a0d14')
GRAY   = colors.HexColor('#f4f6fb')
RED    = colors.HexColor('#ef4444')
GREEN  = colors.HexColor('#22c55e')
WHITE  = colors.white
BLACK  = colors.black
LGRAY  = colors.HexColor('#8892a4')

styles = getSampleStyleSheet()

title_style = ParagraphStyle('BTLTitle', parent=styles['Title'],
    fontSize=20, textColor=DARK, fontName='Helvetica-Bold',
    spaceAfter=4, alignment=TA_CENTER)

subtitle_style = ParagraphStyle('BTLSub', parent=styles['Normal'],
    fontSize=10, textColor=LGRAY, alignment=TA_CENTER, spaceAfter=16)

section_style = ParagraphStyle('BTLSection', parent=styles['Heading2'],
    fontSize=12, textColor=DARK, fontName='Helvetica-Bold',
    spaceBefore=16, spaceAfter=8, borderPad=4)

normal_style = ParagraphStyle('BTLNormal', parent=styles['Normal'],
    fontSize=9, textColor=DARK, spaceAfter=4)

small_style = ParagraphStyle('BTLSmall', parent=styles['Normal'],
    fontSize=8, textColor=LGRAY)

def header_footer(canvas, doc):
    canvas.saveState()
    w, h = A4
    # Header
    canvas.setFillColor(DARK)
    canvas.rect(0, h-50, w, 50, fill=1, stroke=0)
    canvas.setFillColor(GOLD)
    canvas.rect(0, h-52, w, 2, fill=1, stroke=0)
    canvas.setFont('Helvetica-Bold', 14)
    canvas.setFillColor(WHITE)
    canvas.drawString(1.5*cm, h-35, 'BTL')
    canvas.setFont('Helvetica', 10)
    canvas.setFillColor(GOLD)
    canvas.drawString(2.8*cm, h-35, 'Banque Tuniso-Libyenne')
    canvas.setFont('Helvetica', 8)
    canvas.setFillColor(colors.HexColor('#8892a4'))
    canvas.drawRightString(w-1.5*cm, h-35, f"Généré le {data['genere_le']}  |  Par {data['genere_par']}")
    # Footer
    canvas.setFillColor(GRAY)
    canvas.rect(0, 0, w, 28, fill=1, stroke=0)
    canvas.setFillColor(GOLD)
    canvas.rect(0, 28, w, 1, fill=1, stroke=0)
    canvas.setFont('Helvetica', 7)
    canvas.setFillColor(LGRAY)
    canvas.drawString(1.5*cm, 10, 'CONFIDENTIEL — Document interne BTL — Système de Télécompensation SIBTEL')
    canvas.drawRightString(w-1.5*cm, 10, f'Page {doc.page}')
    canvas.restoreState()

doc = SimpleDocTemplate(pdf_path, pagesize=A4,
    leftMargin=1.5*cm, rightMargin=1.5*cm,
    topMargin=2.5*cm, bottomMargin=1.5*cm)

story = []
periode = data['periode']
stats   = data['stats']

# Titre
type_label = {'journalier': 'Journalier', 'hebdomadaire': 'Hebdomadaire', 'mensuel': 'Mensuel'}.get(periode['type'], periode['type'])
story.append(Paragraph(f"Rapport de Télécompensation {type_label}", title_style))
story.append(Paragraph(f"Période : {periode['debut']} — {periode['fin']}", subtitle_style))
story.append(HRFlowable(width='100%', thickness=1.5, color=GOLD, spaceAfter=16))

# KPIs
story.append(Paragraph("Récapitulatif général", section_style))
kpi_data = [
    ['Indicateur', 'Valeur'],
    ['Total fichiers reçus', str(stats['total_fichiers'])],
    ['Fichiers traités avec succès', str(stats['total_traites'])],
    ['Fichiers en erreur', str(stats['total_erreurs'])],
    ['Fichiers en attente validation', str(stats['total_en_attente'])],
    ['Total transactions', str(stats['total_transactions'])],
    ['Transactions valides', str(stats['total_valides'])],
    ['Transactions rejetées', str(stats['total_rejetes'])],
    ['Montant total validé (TND)', f"{stats['montant_total']:,.3f}".replace(',', ' ')],
    ['Total rejets SIBTEL', str(stats['total_rejets'])],
]
t = Table(kpi_data, colWidths=[10*cm, 7*cm])
t.setStyle(TableStyle([
    ('BACKGROUND', (0,0), (-1,0), DARK),
    ('TEXTCOLOR',  (0,0), (-1,0), GOLD),
    ('FONTNAME',   (0,0), (-1,0), 'Helvetica-Bold'),
    ('FONTSIZE',   (0,0), (-1,-1), 9),
    ('BACKGROUND', (0,1), (-1,-1), WHITE),
    ('ROWBACKGROUNDS', (0,1), (-1,-1), [WHITE, GRAY]),
    ('GRID',       (0,0), (-1,-1), 0.5, colors.HexColor('#e2e8f0')),
    ('ALIGN',      (1,0), (1,-1), 'RIGHT'),
    ('FONTNAME',   (0,1), (-1,-1), 'Helvetica'),
    ('TOPPADDING', (0,0), (-1,-1), 6),
    ('BOTTOMPADDING', (0,0), (-1,-1), 6),
    ('LEFTPADDING', (0,0), (-1,-1), 10),
]))
story.append(t)
story.append(Spacer(1, 12))

# Répartition par type
if data.get('par_type'):
    story.append(Paragraph("Répartition par type de valeur", section_style))
    type_data = [['Type', 'Nombre fichiers', 'Montant (TND)']]
    for k, v in data['par_type'].items():
        type_data.append([v['type'], str(v['count']), f"{v['montant']:,.3f}".replace(',', ' ')])
    t2 = Table(type_data, colWidths=[8*cm, 5*cm, 5*cm])
    t2.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), DARK),
        ('TEXTCOLOR',  (0,0), (-1,0), GOLD),
        ('FONTNAME',   (0,0), (-1,0), 'Helvetica-Bold'),
        ('FONTSIZE',   (0,0), (-1,-1), 9),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [WHITE, GRAY]),
        ('GRID',       (0,0), (-1,-1), 0.5, colors.HexColor('#e2e8f0')),
        ('ALIGN',      (1,0), (-1,-1), 'RIGHT'),
        ('FONTNAME',   (0,1), (-1,-1), 'Helvetica'),
        ('TOPPADDING', (0,0), (-1,-1), 6),
        ('BOTTOMPADDING', (0,0), (-1,-1), 6),
        ('LEFTPADDING', (0,0), (-1,-1), 10),
    ]))
    story.append(t2)
    story.append(Spacer(1, 12))

# Listing fichiers
story.append(Paragraph("Listing des fichiers traités", section_style))
if data['fichiers']:
    fich_data = [['Fichier', 'Type', 'Statut', 'Tx', 'Rejets', 'Montant TND', 'Date']]
    for f in data['fichiers']:
        statut_color = GREEN if f['statut'] in ['TRAITE','VALIDE'] else (RED if f['statut'] == 'ERREUR' else colors.HexColor('#f59e0b'))
        fich_data.append([
            Paragraph(f['nom'][:35], small_style),
            f['type'],
            f['statut'],
            str(f['transactions']),
            str(f['rejets']),
            f['montant'],
            f['date'] or '—',
        ])
    t3 = Table(fich_data, colWidths=[5.5*cm, 2*cm, 3*cm, 1.2*cm, 1.2*cm, 3*cm, 2.5*cm])
    t3.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), DARK),
        ('TEXTCOLOR',  (0,0), (-1,0), GOLD),
        ('FONTNAME',   (0,0), (-1,0), 'Helvetica-Bold'),
        ('FONTSIZE',   (0,0), (-1,-1), 7.5),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [WHITE, GRAY]),
        ('GRID',       (0,0), (-1,-1), 0.5, colors.HexColor('#e2e8f0')),
        ('ALIGN',      (3,0), (5,-1), 'RIGHT'),
        ('FONTNAME',   (0,1), (-1,-1), 'Helvetica'),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(t3)
else:
    story.append(Paragraph("Aucun fichier pour cette période.", normal_style))

story.append(Spacer(1, 12))

# Listing rejets
if data['rejets']:
    story.append(Paragraph("Récapitulatif des rejets", section_style))
    rej_data = [['Fichier', 'Code rejet', 'Motif', 'Étape']]
    for r in data['rejets'][:30]:
        rej_data.append([
            Paragraph(str(r['fichier'])[:30], small_style),
            r['code'],
            Paragraph(str(r['motif'])[:50], small_style),
            r['etape'],
        ])
    t4 = Table(rej_data, colWidths=[5*cm, 2.5*cm, 7*cm, 3*cm])
    t4.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#ef4444')),
        ('TEXTCOLOR',  (0,0), (-1,0), WHITE),
        ('FONTNAME',   (0,0), (-1,0), 'Helvetica-Bold'),
        ('FONTSIZE',   (0,0), (-1,-1), 7.5),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [WHITE, colors.HexColor('#fff5f5')]),
        ('GRID',       (0,0), (-1,-1), 0.5, colors.HexColor('#e2e8f0')),
        ('FONTNAME',   (0,1), (-1,-1), 'Helvetica'),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(t4)
    story.append(Spacer(1, 16))

# Signature électronique
story.append(HRFlowable(width='100%', thickness=1, color=GOLD, spaceAfter=8))
sig_data = [
    ['Généré automatiquement par le système BTL-SIBTEL', '', 'Visa du responsable'],
    [f"Date : {data['genere_le']}", '', ''],
    [f"Par : {data['genere_par']}", '', ''],
    ['Signature électronique :', '', '________________________'],
    ['Ce document est certifié conforme aux données du système.', '', ''],
]
t5 = Table(sig_data, colWidths=[9*cm, 2*cm, 7*cm])
t5.setStyle(TableStyle([
    ('FONTSIZE',    (0,0), (-1,-1), 8),
    ('FONTNAME',    (0,0), (-1,-1), 'Helvetica'),
    ('TEXTCOLOR',   (0,0), (-1,-1), LGRAY),
    ('FONTNAME',    (2,0), (2,0), 'Helvetica-Bold'),
    ('TEXTCOLOR',   (2,0), (2,0), DARK),
    ('TOPPADDING',  (0,0), (-1,-1), 3),
    ('ALIGN',       (2,0), (2,-1), 'CENTER'),
]))
story.append(t5)

doc.build(story, onFirstPage=header_footer, onLaterPages=header_footer)
print("PDF généré avec succès")
