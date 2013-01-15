/*******************************************************************

Part of the Fritzing project - http://fritzing.org
Copyright (c) 2007-2012 Fachhochschule Potsdam - http://fh-potsdam.de

Fritzing is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Fritzing is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fritzing.  If not, see <http://www.gnu.org/licenses/>.

********************************************************************

$Revision: 6737 $:
$Author: irascibl@gmail.com $:
$Date: 2012-12-30 22:19:28 +0100 (Sun, 30 Dec 2012) $

********************************************************************/

#include "symbolpaletteitem.h"
#include "../debugdialog.h"
#include "../connectors/connectoritem.h"
#include "../connectors/bus.h"
#include "moduleidnames.h"
#include "../fsvgrenderer.h"
#include "../utils/textutils.h"
#include "../utils/focusoutcombobox.h"
#include "../utils/graphicsutils.h"
#include "../sketch/infographicsview.h"
#include "partlabel.h"

#include <QLineEdit>
#include <QMultiHash>
#include <QMessageBox>

#define VOLTAGE_HASH_CONVERSION 1000000
#define FROMVOLTAGE(v) ((long) (v * VOLTAGE_HASH_CONVERSION))

static QMultiHash<long, QPointer<ConnectorItem> > LocalVoltages;			// Qt doesn't do Hash keys with double
static QMultiHash<QString, QPointer<ConnectorItem> > LocalNetLabels;			
static QList< QPointer<ConnectorItem> > LocalGrounds;
static QList<double> Voltages;
double SymbolPaletteItem::DefaultVoltage = 5;

/////////////////////////////////////////////////////

/*
FocusBugLineEdit::FocusBugLineEdit(QWidget * parent) : QLineEdit(parent)
{
	connect(this, SIGNAL(editingFinished()), this, SLOT(editingFinishedSlot()));
    m_lastEditingFinishedEmit = QTime::currentTime();
}

FocusBugLineEdit::~FocusBugLineEdit()
{
}

void FocusBugLineEdit::editingFinishedSlot() {
    QTime now = QTime::currentTime();
    int d = m_lastEditingFinishedEmit.msecsTo(now);
    DebugDialog::debug(QString("dtime %1").arg(d));
    if (d < 1000) {
        return;
    }

    m_lastEditingFinishedEmit = now;
    emit safeEditingFinished();
}
*/

////////////////////////////////////////

SymbolPaletteItem::SymbolPaletteItem( ModelPart * modelPart, ViewLayer::ViewIdentifier viewIdentifier, const ViewGeometry & viewGeometry, long id, QMenu * itemMenu, bool doLabel)
	: PaletteItem(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel)
{
	if (Voltages.count() == 0) {
		Voltages.append(0.0);
		Voltages.append(3.3);
		Voltages.append(5.0);
		Voltages.append(12.0);
	}

	m_connector0 = m_connector1 = NULL;
	m_voltage = 0;
	m_voltageReference = (modelPart->properties().value("type").compare("voltage reference") == 0);

	if (modelPart->moduleID().endsWith(ModuleIDNames::NetLabelModuleIDName)) {
		m_isNetLabel = true;
		QString label = getLabel();
		if (label.isEmpty()) {
			label = modelPart->properties().value("label");
			if (label.isEmpty()) {
				label = tr("net label");
			}
			modelPart->setLocalProp("label", label);
		}
        setInstanceTitle(label);

		QString direction = getDirection();
		if (direction.isEmpty()) {
			direction = modelPart->properties().value("direction");
			if (direction.isEmpty()) {
				direction = modelPart->moduleID().contains("left", Qt::CaseInsensitive) ? "left" : "right";
			}
			modelPart->setLocalProp("direction", direction);
		}
	}
	else {
		m_isNetLabel = false;

		bool ok;
		double temp = modelPart->localProp("voltage").toDouble(&ok);
		if (ok) {
			m_voltage = temp;
		}
		else {
			temp = modelPart->properties().value("voltage").toDouble(&ok);
			if (ok) {
				m_voltage = SymbolPaletteItem::DefaultVoltage;
			}
			modelPart->setLocalProp("voltage", m_voltage);
		}
		if (!Voltages.contains(m_voltage)) {
			Voltages.append(m_voltage);
		}
	}
}

SymbolPaletteItem::~SymbolPaletteItem() {
	if (m_isNetLabel) {
		foreach (QString key, LocalNetLabels.uniqueKeys()) {
			if (m_connector0) {
				LocalNetLabels.remove(key, m_connector0);
			}
			if (m_connector1) {
				LocalNetLabels.remove(key, m_connector1);
			}
			LocalNetLabels.remove(key, NULL);		// cleans null QPointers
		}
	}
	else {
		if (m_connector0) LocalGrounds.removeOne(m_connector0);
		if (m_connector1) LocalGrounds.removeOne(m_connector1);
		LocalGrounds.removeOne(NULL);   // cleans null QPointers

		foreach (long key, LocalVoltages.uniqueKeys()) {
			if (m_connector0) {
				LocalVoltages.remove(key, m_connector0);
			}
			if (m_connector1) {
				LocalVoltages.remove(key, m_connector1);
			}
			LocalVoltages.remove(key, NULL);		// cleans null QPointers
		}
	}
}

void SymbolPaletteItem::removeMeFromBus(double v) {
	foreach (ConnectorItem * connectorItem, cachedConnectorItems()) {
		if (m_isNetLabel) {
			LocalNetLabels.remove(getLabel(), connectorItem);
		}
		else {
			double nv = useVoltage(connectorItem);
			if (nv == v) {
				//connectorItem->debugInfo(QString("remove %1").arg(useVoltage(connectorItem)));

				bool gotOne = LocalGrounds.removeOne(connectorItem);
				int count = LocalVoltages.remove(FROMVOLTAGE(v), connectorItem);
				LocalVoltages.remove(FROMVOLTAGE(v), NULL);


				if (count == 0 && !gotOne) {
					DebugDialog::debug(QString("removeMeFromBus failed %1 %2 %3 %4")
						.arg(this->id())
						.arg(connectorItem->connectorSharedID())
						.arg(v).arg(nv));
				}
			}
		}
	}
	LocalGrounds.removeOne(NULL);  // keep cleaning these out
}

ConnectorItem* SymbolPaletteItem::newConnectorItem(Connector *connector) 
{
	ConnectorItem * connectorItem = PaletteItemBase::newConnectorItem(connector);

	if (connector->connectorSharedID().compare("connector0") == 0) {
		m_connector0 = connectorItem;
	}
	else if (connector->connectorSharedID().compare("connector1") == 0) {
		m_connector1 = connectorItem;
	}
	else {
		return connectorItem;
	}

	if (m_isNetLabel) {
		LocalNetLabels.insert(getLabel(), connectorItem);
	}
	else if (connectorItem->isGrounded()) {
		LocalGrounds.append(connectorItem);
		//connectorItem->debugInfo("new ground insert");
	}
	else {
		LocalVoltages.insert(FROMVOLTAGE(useVoltage(connectorItem)), connectorItem);
		//connectorItem->debugInfo(QString("new voltage insert %1").arg(useVoltage(connectorItem)));
	}
	return connectorItem;
}

void SymbolPaletteItem::busConnectorItems(Bus * bus, QList<class ConnectorItem *> & items) {
	if (bus == NULL) return;

	PaletteItem::busConnectorItems(bus, items);

	//foreach (ConnectorItem * bc, items) {
		//bc->debugInfo(QString("bc %1").arg(bus->id()));
	//}

	QList< QPointer<ConnectorItem> > mitems;
	if (m_isNetLabel) {
		mitems.append(LocalNetLabels.values(getLabel()));
	}
	else if (bus->id().compare("groundbus", Qt::CaseInsensitive) == 0) {
		mitems.append(LocalGrounds);
	}
	else {
		mitems.append(LocalVoltages.values(FROMVOLTAGE(m_voltage)));
	}
	foreach (ConnectorItem * connectorItem, mitems) {
		if (connectorItem == NULL) continue;

		if (connectorItem->scene() == this->scene()) {
			items.append(connectorItem);
			//connectorItem->debugInfo(QString("symbol bus %1").arg(bus->id()));
		}
	}
}

double SymbolPaletteItem::voltage() {
	return m_voltage;
}

void SymbolPaletteItem::setProp(const QString & prop, const QString & value) {
	if (prop.compare("voltage", Qt::CaseInsensitive) == 0) {
		setVoltage(value.toDouble());
		return;
	}
	if (prop.compare("label", Qt::CaseInsensitive) == 0 && m_isNetLabel) {
		setLabel(value);
		return;
	}
	if (prop.compare("direction", Qt::CaseInsensitive) == 0 && m_isNetLabel) {
        setDirection(value);
		return;
	}

	PaletteItem::setProp(prop, value);
}

void SymbolPaletteItem::setDirection(const QString & value) {
	m_modelPart->setLocalProp("direction", value);
	QString svg = makeSvg();
	resetRenderer(svg);
    resetConnectors(NULL, NULL);
}

void SymbolPaletteItem::setLabel(const QString & label) {
	removeMeFromBus(0);

	m_modelPart->setLocalProp("label", label);

	foreach (ConnectorItem * connectorItem, cachedConnectorItems()) {
		LocalNetLabels.insert(label, connectorItem);
	}

	QString svg = makeSvg();
	resetRenderer(svg);
    resetConnectors(NULL, NULL);
}

void SymbolPaletteItem::setVoltage(double v) {
	removeMeFromBus(m_voltage);

	m_voltage = v;
	m_modelPart->setLocalProp("voltage", v);
	if (!Voltages.contains(v)) {
		Voltages.append(v);
	}

	foreach (ConnectorItem * connectorItem, cachedConnectorItems()) {
		if (connectorItem->isGrounded()) {
			LocalGrounds.append(connectorItem);
			//connectorItem->debugInfo("ground insert");

		}
		else {
			LocalVoltages.insert(FROMVOLTAGE(v), connectorItem);
			//connectorItem->debugInfo(QString("voltage insert %1").arg(useVoltage(connectorItem)));
		}
	}

	if (!m_voltageReference && !m_isNetLabel) return;

	QString svg = makeSvg();
	reloadRenderer(svg, false);

    if (m_partLabel) m_partLabel->displayTextsIf();
}

QString SymbolPaletteItem::makeSvg() {
    if (m_isNetLabel) {
        return makeNetLabelSvg();
    }

	QString path = filename();
	QFile file(filename());
	QString svg;
	if (file.open(QFile::ReadOnly)) {
		svg = file.readAll();
		file.close();
		return replaceTextElement(svg);
	}

	return "";
}

QString SymbolPaletteItem::replaceTextElement(QString svg) {
	double v = ((int) (m_voltage * 1000)) / 1000.0;
	return TextUtils::replaceTextElement(svg, "label", QString::number(v) + "V");
}

QString SymbolPaletteItem::getProperty(const QString & key) {
	if (key.compare("voltage", Qt::CaseInsensitive) == 0) {
		return QString::number(m_voltage);
	}

	return PaletteItem::getProperty(key);
}

double SymbolPaletteItem::useVoltage(ConnectorItem * connectorItem) {
	return (connectorItem->connectorSharedName().compare("GND", Qt::CaseInsensitive) == 0) ? 0 : m_voltage;
}

ConnectorItem * SymbolPaletteItem::connector0() {
	return m_connector0;
}

ConnectorItem * SymbolPaletteItem::connector1() {
	return m_connector1;
}

void SymbolPaletteItem::addedToScene(bool temporary)
{
	if (this->scene()) {
		if (m_isNetLabel) {
			setLabel(getLabel());
		}
		else {
			setVoltage(m_voltage);
		}
	}

    return PaletteItem::addedToScene(temporary);
}

QString SymbolPaletteItem::makeNetLabelSvg() {
    static const double labelFontSize = 200;
    static const double totalHeight = 300;
    static const double arrowWidth = totalHeight / 2;
    static const double strokeWidth = 10;
    static const double halfStrokeWidth = strokeWidth / 2;
    static const double labelOffset = 20;
    static const double labelBaseLine = 220;

    QFont font("Droid Sans", labelFontSize * 72 / GraphicsUtils::StandardFritzingDPI, QFont::Normal);
	QFontMetricsF fm(font);
    double textWidth = fm.width(getLabel()) * GraphicsUtils::StandardFritzingDPI / 72;
    double totalWidth = textWidth + arrowWidth + labelOffset;

	QString header("<?xml version='1.0' encoding='UTF-8' standalone='no'?>\n"
					"<svg xmlns:svg='http://www.w3.org/2000/svg' xmlns='http://www.w3.org/2000/svg' version='1.2' baseProfile='tiny' \n"
					"width='%1in' height='%2in' viewBox='0 0 %3 %4' >\n"
					"<g id='schematic' >\n"
                    );

    bool goLeft = (getDirection() == "left");
    double offset = goLeft ? arrowWidth : 0;

    QString svg = header.arg(totalWidth / 1000).arg(totalHeight / 1000).arg(totalWidth).arg(totalHeight);

    QString pin = QString("<rect id='connector0pin' x='%1' y='%2' width='%3' height='%4' fill='none' stroke='none' stroke-width='0' />\n");
    QString terminal = QString("<rect id='connector0terminal' x='%1' y='%2' width='0.1' height='0.1' fill='none' stroke='none' stroke-width='0' />\n");

    QString points = QString("%1,%2 %3,%4 %5,%4 %5,%6 %3,%6");
    if (goLeft) {
        points = points.arg(halfStrokeWidth).arg(totalHeight / 2)
                        .arg(arrowWidth).arg(halfStrokeWidth)
                        .arg(totalWidth - halfStrokeWidth).arg(totalHeight - halfStrokeWidth);
        terminal = terminal.arg(0).arg(totalHeight / 2);
        pin = pin.arg(0).arg(0).arg(arrowWidth).arg(totalHeight);
    }
    else {
        points = points.arg(totalWidth - halfStrokeWidth).arg(totalHeight / 2)
                        .arg(totalWidth - arrowWidth).arg(halfStrokeWidth)
                        .arg(halfStrokeWidth).arg(totalHeight - halfStrokeWidth);
        terminal = terminal.arg(totalWidth).arg(totalHeight / 2);
        pin = pin.arg(totalWidth - arrowWidth - 0.1).arg(0).arg(arrowWidth).arg(totalHeight);
    }

    svg += pin;
    svg += terminal;
    svg += QString("<polygon fill='white' stroke='#000000' stroke-width='%1' points='%2' />\n")
                    .arg(strokeWidth)
                    .arg(points);

    svg += QString("<text id='label' x='%1' y='%2' fill='#000000' font-family='DroidSans' font-size='%3'>%4</text>\n")
            .arg(labelOffset + offset)
            .arg(labelBaseLine)
            .arg(labelFontSize)
            .arg(getLabel());


    svg += "</g>\n</svg>\n";
 
    return svg;
}

QString SymbolPaletteItem::retrieveNetLabelSvg(ViewLayer::ViewLayerID viewLayerID, QHash<QString, QString> & svgHash, bool blackOnly, double dpi) {
    Q_UNUSED(svgHash);
    QString svg = makeNetLabelSvg();
    return PaletteItemBase::normalizeSvg(svg, viewLayerID, blackOnly, dpi);
}

QString SymbolPaletteItem::retrieveSvg(ViewLayer::ViewLayerID viewLayerID, QHash<QString, QString> & svgHash, bool blackOnly, double dpi) 
{
    if (m_isNetLabel) {
        return retrieveNetLabelSvg(viewLayerID, svgHash, blackOnly, dpi);
    }

	QString svg = PaletteItem::retrieveSvg(viewLayerID, svgHash, blackOnly, dpi);
	if (m_voltageReference) {
		switch (viewLayerID) {
			case ViewLayer::Schematic:
				return replaceTextElement(svg);
			default:
				break;
		}
	}

	return svg; 
}

bool SymbolPaletteItem::collectExtraInfo(QWidget * parent, const QString & family, const QString & prop, const QString & value, bool swappingEnabled, QString & returnProp, QString & returnValue, QWidget * & returnWidget)
{
	if ((prop.compare("voltage", Qt::CaseInsensitive) == 0) && 
		(moduleID().compare(ModuleIDNames::GroundModuleIDName) != 0)) 
	{
		FocusOutComboBox * edit = new FocusOutComboBox(parent);
		edit->setEnabled(swappingEnabled);
		int ix = 0;
		foreach (double v, Voltages) {
			edit->addItem(QString::number(v));
			if (v == m_voltage) {
				edit->setCurrentIndex(ix);
			}
			ix++;
		}

		QDoubleValidator * validator = new QDoubleValidator(edit);
		validator->setRange(-9999.99, 9999.99, 2);
		validator->setNotation(QDoubleValidator::StandardNotation);
		edit->setValidator(validator);

		edit->setObjectName("infoViewComboBox");


		connect(edit, SIGNAL(currentIndexChanged(const QString &)), this, SLOT(voltageEntry(const QString &)));
		returnWidget = edit;	

		returnValue = m_voltage;
		returnProp = tr("voltage");
		return true;
	}

	if (prop.compare("label", Qt::CaseInsensitive) == 0 && m_isNetLabel) 
	{
		QLineEdit * edit = new QLineEdit(parent);
		edit->setEnabled(swappingEnabled);
		edit->setText(getLabel());
		edit->setObjectName("infoViewLineEdit");

		connect(edit, SIGNAL(editingFinished()), this, SLOT(labelEntry()));
		returnWidget = edit;	

		returnValue = getLabel();
		returnProp = tr("label");
		return true;
	}

	return PaletteItem::collectExtraInfo(parent, family, prop, value, swappingEnabled, returnProp, returnValue, returnWidget);
}

void SymbolPaletteItem::voltageEntry(const QString & text) {
	InfoGraphicsView * infoGraphicsView = InfoGraphicsView::getInfoGraphicsView(this);
	if (infoGraphicsView != NULL) {
		infoGraphicsView->setVoltage(text.toDouble(), true);
	}
}

void SymbolPaletteItem::labelEntry() {
	QLineEdit * edit = qobject_cast<QLineEdit *>(sender());
	if (edit == NULL) return;

	QString current = getLabel();
	if (edit->text().compare(current) == 0) return;

    if (edit->text().isEmpty()) {
		QMessageBox::warning(NULL, tr("Net labels"), tr("Net labels cannot be blank"));
        return;
    }

	InfoGraphicsView * infoGraphicsView = InfoGraphicsView::getInfoGraphicsView(this);
	if (infoGraphicsView != NULL) {
		infoGraphicsView->setProp(this, "label", ItemBase::TranslatedPropertyNames.value("label"), current, edit->text(), true);
	}
}

ItemBase::PluralType SymbolPaletteItem::isPlural() {
    if (m_isNetLabel) return Plural;

	return Singular;
}

bool SymbolPaletteItem::hasPartNumberProperty()
{
	return false;
}

ViewLayer::ViewIdentifier SymbolPaletteItem::useViewIdentifierForPixmap(ViewLayer::ViewIdentifier vid, bool) 
{
    if (vid == ViewLayer::SchematicView) {
        return ViewLayer::IconView;
    }

    return ViewLayer::UnknownView;
}

bool SymbolPaletteItem::hasPartLabel() {
	return !m_isNetLabel;
}

bool SymbolPaletteItem::isNetLabel() {
	return m_isNetLabel;
}

QString SymbolPaletteItem::getLabel() {
    return  modelPart()->localProp("label").toString();
}

QString SymbolPaletteItem::getDirection() {
    return  modelPart()->localProp("direction").toString();
}

void SymbolPaletteItem::setAutoroutable(bool ar) {
	m_viewGeometry.setAutoroutable(ar);
}

bool SymbolPaletteItem::getAutoroutable() {
	return m_viewGeometry.getAutoroutable();
}
