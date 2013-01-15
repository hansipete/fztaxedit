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

$Revision: 6505 $:
$Author: irascibl@gmail.com $:
$Date: 2012-10-03 10:14:52 +0200 (Wed, 03 Oct 2012) $

********************************************************************/

#include "groundplane.h"
#include "../connectors/connectoritem.h"
#include "../fsvgrenderer.h"
#include "../model/modelpart.h"
#include "../utils/graphicsutils.h"
#include "../svg/svgfilesplitter.h"
#include "../svg/groundplanegenerator.h"
#include "../sketch/infographicsview.h"

#include <QPainterPathStroker>

// TODO: 
//
//	delete part if copper fill fails, and remove item from undo stack
//	maybe that means the generator has to kick in on the dropped item, and if it fails then
//	there is no undo stack to worry about; if it succeeds, then copy the svg prop.
//
//	does single poly need a connector?
//

QString GroundPlane::fillTypeIndividual = "individual";
QString GroundPlane::fillTypeGround = "ground";
QString GroundPlane::fillTypePlain = "plain";
QString GroundPlane::fillTypeNone = "none";


static QString IconSvg;

void loadIconSvg() 
{
	if (IconSvg.isEmpty()) {
		QFile f(":resources/parts/svg/core/icon/groundplane.svg");
		if (f.open(QFile::ReadOnly)) {
			IconSvg = f.readAll();
			f.close();
		}
	}
}

/////////////////////////////////////////////////////////

GroundPlane::GroundPlane( ModelPart * modelPart, ViewLayer::ViewIdentifier viewIdentifier,  const ViewGeometry & viewGeometry, long id, QMenu * itemMenu, bool doLabel) 
	: PaletteItem(modelPart, viewIdentifier,  viewGeometry,  id, itemMenu, doLabel)
{
	m_connector0 = NULL;
}

bool GroundPlane::setUpImage(ModelPart * modelPart, ViewLayer::ViewIdentifier viewIdentifier, const LayerHash & viewLayers, ViewLayer::ViewLayerID viewLayerID, ViewLayer::ViewLayerSpec viewLayerSpec, bool doConnectors, LayerAttributes & layerAttributes, QString & error)
{
	bool result = PaletteItem::setUpImage(modelPart, viewIdentifier, viewLayers, viewLayerID, viewLayerSpec, doConnectors, layerAttributes, error);

	if (doConnectors) {
		foreach (ConnectorItem * item, cachedConnectorItems()) {
			// there is only one
			m_connector0 = item;
			break;
		}
	}

	return result;
}

void GroundPlane::saveParams() {
}

void GroundPlane::getParams() {
}

QString GroundPlane::retrieveSvg(ViewLayer::ViewLayerID viewLayerID, QHash<QString, QString> & svgHash, bool blackOnly, double dpi) 
{
	QString xml = "";
	if (viewLayerID == ViewLayer::GroundPlane0 || viewLayerID == ViewLayer::GroundPlane1) {
		xml = prop("svg");

		if (!xml.isEmpty()) {
            return PaletteItemBase::normalizeSvg(xml, viewLayerID, blackOnly, dpi);
		}
	}

	return PaletteItemBase::retrieveSvg(viewLayerID, svgHash, blackOnly, dpi);
}

ConnectorItem * GroundPlane::connector0() {
	return m_connector0;
}

bool GroundPlane::hasCustomSVG() {
	switch (m_viewIdentifier) {
		case ViewLayer::PCBView:
			return true;
		default:
			return ItemBase::hasCustomSVG();
	}
}

void GroundPlane::setProp(const QString & prop, const QString & value) {
	if (prop.compare("svg", Qt::CaseInsensitive) == 0) {
		setSvg(value);
		return;
	}

	PaletteItemBase::setProp(prop, value);
}

void GroundPlane::addedToScene(bool temporary) 
{
	if (m_viewLayerID == ViewLayer::GroundPlane0 || m_viewLayerID == ViewLayer::GroundPlane1) {
		if (this->scene()) {
			QString svg;
			if (temporary) {
				loadIconSvg();
				svg = IconSvg;
			}
			else {
				svg = prop("svg");
			}
			if (!svg.isEmpty()) {
				setSvgAux(svg);
			}
		}
	}

	PaletteItem::addedToScene(temporary);
}


void GroundPlane::setSvg(const QString & svg) {
	modelPart()->setLocalProp("svg", svg);
	setSvgAux(svg);
}

void GroundPlane::setSvgAux(const QString & svg) {
	if (svg.isEmpty()) return;
	if (m_viewIdentifier == ViewLayer::BreadboardView || m_viewIdentifier == ViewLayer::SchematicView) return;

	QString xmlName = ViewLayer::viewLayerXmlNameFromID(m_viewLayerID);
	SvgFileSplitter	splitter;
	QString cpy = svg;
	bool result = splitter.splitString(cpy, xmlName);
	if (result) {
		resetRenderer(svg);
		if (m_connector0) {
			QPainterPath painterPath = splitter.painterPath(GraphicsUtils::SVGDPI, GroundPlaneGenerator::ConnectorName);
			m_connector0->setRect(painterPath.boundingRect());
			m_connector0->setShape(painterPath);
		}
		//QPainterPath painterPath = splitter.painterPath(GraphicsUtils::SVGDPI, xmlName);
		//this->setShape(painterPath);  
	}
}

QString GroundPlane::svg() {
	return prop("svg");
}

bool GroundPlane::hasPartLabel() {
	
	return false;
}

void GroundPlane::loadLayerKin( const LayerHash & viewLayers, ViewLayer::ViewLayerSpec viewLayerSpec) {
	Q_UNUSED(viewLayers);
	Q_UNUSED(viewLayerSpec);
	// GroundPlane is only on one side or another, not both
}

bool GroundPlane::hasPartNumberProperty()
{
	return false;
}

bool GroundPlane::rotationAllowed() {
	return false;
}

bool GroundPlane::rotation45Allowed() {
	return false;
}

ItemBase::PluralType GroundPlane::isPlural() {
	return Singular;
}

bool GroundPlane::canEditPart() {
	return false;
}

QString GroundPlane::generateSvg() {
	InfoGraphicsView * infoGraphicsView = InfoGraphicsView::getInfoGraphicsView(this);
	if (infoGraphicsView == NULL) return "";

	QPointF q = this->boundingRect().center() + this->pos();
	//QPointF r = this->pos() + m_dropOffset;
	return infoGraphicsView->generateCopperFillUnit(this, q);   
}

void GroundPlane::setDropOffset(QPointF offset) 
{
	InfoGraphicsView * infoGraphicsView = InfoGraphicsView::getInfoGraphicsView(this);
	if (infoGraphicsView == NULL) return;
    
    m_dropOffset = offset;
	modelPart()->setLocalProp("fillType", fillTypeIndividual);
	QString svg = generateSvg();
    bool resolve = true;
	if (svg.isEmpty()) {
		loadIconSvg();
		svg = IconSvg;
        resolve = false;
	}
	setSvg(svg);

	infoGraphicsView->resolveTemporary(resolve, this);   
}

void GroundPlane::setShape(QPainterPath & pp) {
	QPen pen;
	m_shape = GraphicsUtils::shapeFromPath(pp, pen, pen.widthF(), true);
}

QPainterPath GroundPlane::shape() const
{
	if (!m_shape.isEmpty()) {
		return m_shape;
	}
    
    return PaletteItemBase::shape();
}

ViewLayer::ViewIdentifier GroundPlane::useViewIdentifierForPixmap(ViewLayer::ViewIdentifier vid, bool) 
{
    if (vid == ViewLayer::PCBView) {
        return ViewLayer::IconView;
    }

    return ViewLayer::UnknownView;
}
