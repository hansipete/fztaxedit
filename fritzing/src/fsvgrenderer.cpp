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

$Revision: 6650 $:
$Author: irascibl@gmail.com $:
$Date: 2012-11-22 23:19:08 +0100 (Thu, 22 Nov 2012) $

********************************************************************/

#include "fsvgrenderer.h"
#include "debugdialog.h"
#include "svg/svgfilesplitter.h"
#include "utils/textutils.h"
#include "utils/graphicsutils.h"
#include "connectors/svgidlayer.h"

#include <QRegExp>
#include <QTextStream>
#include <QPainter>
#include <QCoreApplication>
#include <QGraphicsSvgItem>
#include <qnumeric.h>

QString FSvgRenderer::NonConnectorName("nonconn");

static ConnectorInfo VanillaConnectorInfo;

FSvgRenderer::FSvgRenderer(QObject * parent) : QSvgRenderer(parent)
{
	m_defaultSizeF = QSizeF(0,0);
}

FSvgRenderer::~FSvgRenderer()
{
	clearConnectorInfoHash(m_connectorInfoHash);
	clearConnectorInfoHash(m_nonConnectorInfoHash);
}

void FSvgRenderer::initNames() {
    VanillaConnectorInfo.gotPath = VanillaConnectorInfo.gotCircle = false;	
}

void FSvgRenderer::clearConnectorInfoHash(QHash<QString, ConnectorInfo *> & hash) {
	foreach (ConnectorInfo * connectorInfo, hash.values()) {
		delete connectorInfo;
	}
	hash.clear();
}

void FSvgRenderer::cleanup() {

}

QByteArray FSvgRenderer::loadSvg(const QString & filename) {
	QStringList strings;
	QString string;
	return loadSvg(filename, strings, strings, strings, string, string, false);
}

QByteArray FSvgRenderer::loadSvg(const QString & filename, const QStringList & connectorIDs, const QStringList & terminalIDs, const QStringList & legIDs, const QString & setColor, const QString & colorElementID, bool findNonConnectors) {
	if (!QFileInfo(filename).exists() || !QFileInfo(filename).isFile()) {
		return QByteArray();
	}

    QFile file(filename);
    if (!file.open(QFile::ReadOnly | QFile::Text)) {
		return QByteArray();
	}

	QByteArray contents = file.readAll();
	file.close();

	if (contents.length() <= 0) return QByteArray();

	return loadAux(contents, filename, connectorIDs, terminalIDs, legIDs, setColor, colorElementID, findNonConnectors);

}

bool FSvgRenderer::loadSvgString(const QString & svg) {
	QByteArray byteArray(svg.toUtf8());
	QByteArray result = loadSvg(byteArray, "", true);
    return (!result.isEmpty());
}

bool FSvgRenderer::loadSvgString(const QString & svg, QString & newSvg) {
	QByteArray byteArray(svg.toUtf8());
	QByteArray result = loadSvg(byteArray, "", true);
    newSvg = QString(result);
    return (!result.isEmpty());
}

QByteArray FSvgRenderer::loadSvg(const QByteArray & contents, const QString & filename, bool findNonConnectors) {
	QStringList strings;
	QString string;
	return loadSvg(contents, filename, strings, strings, strings, string, string, findNonConnectors);
}

QByteArray FSvgRenderer::loadSvg(const QByteArray & contents, const QString & filename, const QStringList & connectorIDs, const QStringList & terminalIDs, const QStringList & legIDs, const QString & setColor, const QString & colorElementID, bool findNonConnectors) {
	return loadAux(contents, filename, connectorIDs, terminalIDs, legIDs, setColor, colorElementID, findNonConnectors);
}

QByteArray FSvgRenderer::loadAux(const QByteArray & theContents, const QString & filename, const QStringList & connectorIDs, const QStringList & terminalIDs, const QStringList & legIDs, const QString & setColor, const QString & colorElementID, bool findNonConnectors) 
{
	QByteArray cleanContents(theContents);
	bool cleaned = false;

    QString string(cleanContents);
    if (TextUtils::fixMuch(string, false)) {
		cleaned = true;
	}
	if (TextUtils::fixPixelDimensionsIn(string)) {
        cleaned = true;
    }
    if (cleaned) {
        cleanContents = string.toUtf8();
    }

	if (connectorIDs.count() > 0 || !setColor.isEmpty() || findNonConnectors) {
		QString errorStr;
		int errorLine;
		int errorColumn;
		QDomDocument doc;
		if (!doc.setContent(cleanContents, &errorStr, &errorLine, &errorColumn)) {
			DebugDialog::debug(QString("renderer loadAux failed %1 %2 %3 %4").arg(filename).arg(errorStr).arg(errorLine).arg(errorColumn));
		}

		bool resetContents = false;

		QDomElement root = doc.documentElement();
		if (!setColor.isEmpty()) {
			QDomElement element = TextUtils::findElementWithAttribute(root, "id", colorElementID);
			if (!element.isNull()) {
				QStringList exceptions;
				exceptions << "black" << "#000000";
				SvgFileSplitter::fixColorRecurse(element, setColor, exceptions);
				resetContents = true;
			}
		}
		if (connectorIDs.count() > 0) {
			bool init =  initConnectorInfo(doc, connectorIDs, terminalIDs, legIDs, filename);
			resetContents = resetContents || init;
		}
		if (findNonConnectors) {
			initNonConnectorInfo(doc, filename);
		}

		if (resetContents) {
			cleanContents = TextUtils::removeXMLEntities(doc.toString()).toUtf8();
		}
	}


	/*
	cleanContents = doc.toByteArray();

	//QFile file("all.txt");
	//if (file.open(QIODevice::Append)) {
		//QTextStream t(&file);
		//t << cleanContents;
		//file.close();
	//}

	*/

	//DebugDialog::debug(cleanContents.data());

	QXmlStreamReader xml(cleanContents);
	bool result = determineDefaultSize(xml);
	if (!result) {
		return QByteArray();
	}

	result = QSvgRenderer::load(cleanContents);
	if (result) {
		m_filename = filename;
		return cleanContents;
	}

	return QByteArray();
}

bool FSvgRenderer::fastLoad(const QByteArray & contents) {
	return QSvgRenderer::load(contents);
}

const QString & FSvgRenderer::filename() {
	return m_filename;
}

QPixmap * FSvgRenderer::getPixmap(QSvgRenderer * renderer, QSize size) 
{
	QPixmap *pixmap = new QPixmap(size);
	pixmap->fill(Qt::transparent);
	QPainter painter(pixmap);
	// preserve aspect ratio
	QSizeF def = renderer->defaultSize();
    FSvgRenderer * frenderer = qobject_cast<FSvgRenderer *>(renderer);
    if (frenderer) {
        def = frenderer->defaultSizeF();
    }
	double newW = size.width();
	double newH = newW * def.height() / def.width();
	if (newH > size.height()) {
		newH = size.height();
		newW = newH * def.width() / def.height();
	}
	QRectF bounds((size.width() - newW) / 2.0, (size.height() - newH) / 2.0, newW, newH);
	renderer->render(&painter, bounds);
	painter.end();

    return pixmap;
}

bool FSvgRenderer::determineDefaultSize(QXmlStreamReader & xml)
{
	QSizeF size = parseForWidthAndHeight(xml);

	m_defaultSizeF = QSizeF(size.width() * GraphicsUtils::SVGDPI, size.height() * GraphicsUtils::SVGDPI);
	return (size.width() != 0 && size.height() != 0);
}

QSizeF FSvgRenderer::parseForWidthAndHeight(QXmlStreamReader & xml)
{
    QSizeF size = TextUtils::parseForWidthAndHeight(xml);
    if (size.width() != 0 && size.height() != 0) return size;

	QIODevice * device = xml.device();
	DebugDialog::debug("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
	DebugDialog::debug("bad width and/or bad height in svg:");
	if (device) {
		device->reset();
		QString string(device->readAll());
		DebugDialog::debug(string);
	}
	DebugDialog::debug("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");

	return size;
}

QSizeF FSvgRenderer::defaultSizeF() {
	if (m_defaultSizeF.width() == 0 && m_defaultSizeF.height() == 0) {
		return defaultSize();
	}

	return m_defaultSizeF;
}


void FSvgRenderer::initNonConnectorInfo(QDomDocument & domDocument, const QString & filename)
{
	clearConnectorInfoHash(m_nonConnectorInfoHash);
	QDomElement root = domDocument.documentElement();
	initNonConnectorInfoAux(root, filename);
}

void FSvgRenderer::initNonConnectorInfoAux(QDomElement & element, const QString & filename)
{
	QString id = element.attribute("id");
	if (id.startsWith(NonConnectorName, Qt::CaseInsensitive)) {
		ConnectorInfo * connectorInfo = initConnectorInfoStruct(element, filename);
		m_nonConnectorInfoHash.insert(id, connectorInfo);
	}
	QDomElement child = element.firstChildElement();
	while (!child.isNull()) {
		initNonConnectorInfoAux(child, filename);
		child = child.nextSiblingElement();
	}
}

bool FSvgRenderer::initConnectorInfo(QDomDocument & domDocument, const QStringList & connectorIDs, const QStringList & terminalIDs, const QStringList & legIDs, const QString & filename)
{
	bool result = false;
	clearConnectorInfoHash(m_connectorInfoHash);
	QDomElement root = domDocument.documentElement();
	initConnectorInfoAux(root, connectorIDs, filename);
	if (terminalIDs.count() > 0) {
		initTerminalInfoAux(root, connectorIDs, terminalIDs);
	}
	if (legIDs.count() > 0) {
		initLegInfoAux(root, connectorIDs, legIDs, result);
	}

	return result;
}

void FSvgRenderer::initLegInfoAux(QDomElement & element, const QStringList & connectorIDs, const QStringList & legIDs, bool & gotOne)
{
	QString id = element.attribute("id");
	if (!id.isEmpty()) {
		int ix = legIDs.indexOf(id);
		if (ix >= 0) {
			//DebugDialog::debug("init leg info " + id);
			//foreach (QString lid, legIDs) {
			//	DebugDialog::debug("\tleg id:" + lid);
			//}

			element.setTagName("g");			// don't want this element to actually be drawn
			gotOne = true;
			ConnectorInfo * connectorInfo = m_connectorInfoHash.value(connectorIDs.at(ix), NULL);
			if (connectorInfo) {
				//QString temp;
				//QTextStream stream(&temp);
				//element.save(stream, 0);
				//DebugDialog::debug("\t matched " + connectorIDs.at(ix) + " " + temp);
				connectorInfo->legMatrix = TextUtils::elementToMatrix(element);
				connectorInfo->legColor = element.attribute("stroke");
				connectorInfo->legLine = QLineF();
				connectorInfo->legStrokeWidth = 0;
				initLegInfoAux(element, connectorInfo);
			}
			// don't return here, might miss other legs
		}
	}

	QDomElement child = element.firstChildElement();
	while (!child.isNull()) {
		initLegInfoAux(child, connectorIDs, legIDs, gotOne);
		child = child.nextSiblingElement();
	}
}

bool FSvgRenderer::initLegInfoAux(QDomElement & element, ConnectorInfo * connectorInfo) 
{
	bool ok;
	double sw = element.attribute("stroke-width").toDouble(&ok);
	if (!ok) return false;

	double x1 = element.attribute("x1").toDouble(&ok);
	if (!ok) return false;

	double y1 = element.attribute("y1").toDouble(&ok);
	if (!ok) return false;

	double x2 = element.attribute("x2").toDouble(&ok);
	if (!ok) return false;

	double y2 = element.attribute("y2").toDouble(&ok);
	if (!ok) return false;

	connectorInfo->legStrokeWidth = sw;
	connectorInfo->legLine = QLineF(x1, y1, x2, y2);
	return true;
}

void FSvgRenderer::initTerminalInfoAux(QDomElement & element, const QStringList & connectorIDs, const QStringList & terminalIDs)
{
	QString id = element.attribute("id");
	if (!id.isEmpty()) {
		int ix = terminalIDs.indexOf(id);
		if (ix >= 0) {
			ConnectorInfo * connectorInfo = m_connectorInfoHash.value(connectorIDs.at(ix), NULL);
			if (connectorInfo) {
				connectorInfo->terminalMatrix = TextUtils::elementToMatrix(element);
			}
			// don't return here, might miss other terminal ids
		}
	}

	QDomElement child = element.firstChildElement();
	while (!child.isNull()) {
		initTerminalInfoAux(child, connectorIDs, terminalIDs);
		child = child.nextSiblingElement();
	}
}

void FSvgRenderer::initConnectorInfoAux(QDomElement & element, const QStringList & connectorIDs, const QString & filename)
{
	QString id = element.attribute("id");
	if (!id.isEmpty()) {
		if (connectorIDs.contains(id)) {
			ConnectorInfo * connectorInfo = initConnectorInfoStruct(element, filename);
			m_connectorInfoHash.insert(id, connectorInfo);
		}
		// don't return here, might miss other connectors
	}

	QDomElement child = element.firstChildElement();
	while (!child.isNull()) {
		initConnectorInfoAux(child, connectorIDs, filename);
		child = child.nextSiblingElement();
	}
}

ConnectorInfo * FSvgRenderer::initConnectorInfoStruct(QDomElement & connectorElement, const QString & filename) {
	ConnectorInfo * connectorInfo = new ConnectorInfo();
	connectorInfo->radius = connectorInfo->strokeWidth = 0;
	connectorInfo->gotPath = connectorInfo->gotCircle = false;

	if (connectorElement.isNull()) return connectorInfo;

	connectorInfo->matrix = TextUtils::elementToMatrix(connectorElement);
	initConnectorInfoStructAux(connectorElement, connectorInfo, filename);
	return connectorInfo;
}

bool FSvgRenderer::initConnectorInfoStructAux(QDomElement & element, ConnectorInfo * connectorInfo, const QString & filename) 
{
	// right now we only handle circles
	if (element.nodeName().compare("circle") != 0) {
		QDomElement child = element.firstChildElement();
		while (!child.isNull()) {
			if (initConnectorInfoStructAux(child, connectorInfo, filename)) return true;

			child = child.nextSiblingElement();
		}

        if (element.nodeName().compare("path") == 0) {
            connectorInfo->gotPath = true;
        }

		return false;
	}

	bool ok;
	element.attribute("cx").toDouble(&ok);
	if (!ok) return false;

	element.attribute("cy").toDouble(&ok);
	if (!ok) return false;

	double r = element.attribute("r").toDouble(&ok);
	if (!ok) return false;

	double sw = element.attribute("stroke-width").toDouble(&ok);	
	if (!ok) {
        QDomElement parent = element.parentNode().toElement();
        while (!parent.isNull()) {
            sw = element.attribute("stroke-width").toDouble(&ok);
            if (ok) {
                break;
            }

            parent = parent.parentNode().toElement();
        }

        if (!ok) {
            QString text;
            QTextStream stream(&text);
            element.save(stream, 0);
            //DebugDialog::debug(QString("no circle stroke width set in %1: %2").arg(filename).arg(text));
            element.setAttribute("stroke-width", 1);
            sw = 1;

		    //QString strokewidth("stroke-width");
		    //QString s = element.attribute("style");
		    //SvgFileSplitter::fixStyleAttribute(connectorElement, s, strokewidth);
		    //sw = connectorElement.attribute("stroke-width").toDouble(&ok);
		    //if (!ok) {
			    //return false;
		    //}
        }
	}

	QMatrix matrix = TextUtils::elementToMatrix(element);
	if (!matrix.isIdentity()) {
		QRectF r1(0,0,r,r);
		QRectF r2 = matrix.mapRect(r1);
		if (r2.width() != r1.width()) {
			r = r2.width();
			sw = sw * r2.width() / r1.width();
		}
	}

	//DebugDialog::debug("got a circle");
	connectorInfo->gotCircle = true;
	//connectorInfo->cbounds.setRect(cx - r - (sw / 2.0), cy - r - (sw / 2.0), (r * 2) + sw, (r * 2) + sw);
	connectorInfo->radius = r;
	connectorInfo->strokeWidth = sw;
	return true;
}

ConnectorInfo * FSvgRenderer::getConnectorInfo(const QString & connectorID) {
	return m_connectorInfoHash.value(connectorID, &VanillaConnectorInfo);
}

bool FSvgRenderer::setUpConnector(SvgIdLayer * svgIdLayer, bool ignoreTerminalPoint) {

	if (svgIdLayer == NULL) return false;

	if (svgIdLayer->m_processed) {
		// hybrids are not visible in some views
		return svgIdLayer->m_svgVisible || svgIdLayer->m_hybrid;
	}

	svgIdLayer->m_processed = true;

	QString connectorID = svgIdLayer->m_svgId;

	// boundsOnElement seems to include any matrix on the element itself.
	// I would swear this wasn't true before Qt4.7, but maybe I am crazy
	QRectF bounds = this->boundsOnElement(connectorID);	

	if (bounds.isNull() && !svgIdLayer->m_hybrid) {		// hybrids can have zero size
		svgIdLayer->m_svgVisible = false;		
		DebugDialog::debug("renderer::setupconnector: null bounds");
		return false;
	}

	QSizeF defaultSizeF = this->defaultSizeF();
	QRectF viewBox = this->viewBoxF();

	ConnectorInfo * connectorInfo = getConnectorInfo(connectorID);	

	/*
	DebugDialog::debug(QString("connectorid:%1 m11:%2 m12:%3 m21:%4 m22:%5 dx:%6 dy:%7")
						.arg(connectorID)
						.arg(connectorInfo->matrix.m11())
						.arg(connectorInfo->matrix.m12())
						.arg(connectorInfo->matrix.m21())
						.arg(connectorInfo->matrix.m22())
						.arg(connectorInfo->matrix.dx())
						.arg(connectorInfo->matrix.dy()),
				bounds);
	*/

	
	/*DebugDialog::debug(QString("identity matrix %11 %1 %2, viewbox: %3 %4 %5 %6, bounds: %7 %8 %9 %10, size: %12 %13").arg(m_modelPart->title()).arg(connectorSharedID())
					   .arg(viewBox.x()).arg(viewBox.y()).arg(viewBox.width()).arg(viewBox.height())
					   .arg(bounds.x()).arg(bounds.y()).arg(bounds.width()).arg(bounds.height())
					   .arg(viewIdentifier)
					   .arg(defaultSizeF.width()).arg(defaultSizeF.height())
	);
	*/

	// some strangeness in the way that svg items and non-svg items map to screen space
	// might be a qt problem.
	//QMatrix matrix0 = connectorInfo->matrix * this->matrixForElement(connectorID);  
	//QRectF r1 = matrix0.mapRect(bounds);

    QMatrix elementMatrix = this->matrixForElement(connectorID);
	QRectF r1 = elementMatrix.mapRect(bounds);

	if (connectorInfo != NULL) {
        if (connectorInfo->gotCircle) {
            QLineF l(0,0,connectorInfo->radius, 0);
            QLineF lm = elementMatrix.map(l);
		    svgIdLayer->m_radius = lm.length() * defaultSizeF.width() / viewBox.width();

            QLineF k(0,0,connectorInfo->strokeWidth, 0);
            QLineF km = elementMatrix.map(l);
		    svgIdLayer->m_strokeWidth = km.length() * defaultSizeF.width() / viewBox.width();
		    //bounds = connectorInfo->cbounds;
        }
        else if (connectorInfo->gotPath) {
            svgIdLayer->m_path = true;
        }
	}


	/*
	svgIdLayer->m_rect.setRect(r1.x() * defaultSize.width() / viewBox.width(), 
							   r1.y() * defaultSize.height() / viewBox.height(), 
							   r1.width() * defaultSize.width() / viewBox.width(), 
							   r1.height() * defaultSize.height() / viewBox.height());
	*/
	svgIdLayer->m_rect.setRect(r1.x() * defaultSizeF.width() / viewBox.width(), 
							   r1.y() * defaultSizeF.height() / viewBox.height(), 
							   r1.width() * defaultSizeF.width() / viewBox.width(), 
							   r1.height() * defaultSizeF.height() / viewBox.height());

	svgIdLayer->m_svgVisible = !bounds.isNull();
	//if (!svgIdLayer->m_svgVisible) {
		//DebugDialog::debug("not vis");
	//}
	svgIdLayer->m_point = calcTerminalPoint(svgIdLayer->m_terminalId, svgIdLayer->m_rect, ignoreTerminalPoint, viewBox, connectorInfo->terminalMatrix);
	calcLeg(svgIdLayer, viewBox, connectorInfo);
	
	return true;
}

void FSvgRenderer::calcLeg(SvgIdLayer * svgIdLayer, const QRectF & viewBox, ConnectorInfo * connectorInfo)
{
	if (svgIdLayer->m_legId.isEmpty()) return;

	svgIdLayer->m_legColor = connectorInfo->legColor;

	QSizeF defaultSizeF = this->defaultSizeF();
	svgIdLayer->m_legStrokeWidth = connectorInfo->legStrokeWidth * defaultSizeF.width() / viewBox.width();

	/*
	DebugDialog::debug(	QString("calcleg leg %1 %2 %3 %4")
		.arg(connectorInfo->legLine.p1().x())
		.arg(connectorInfo->legLine.p1().y())
		.arg(connectorInfo->legLine.p2().x())
		.arg(connectorInfo->legLine.p2().y())
		);
	*/

	QMatrix matrix = this->matrixForElement(svgIdLayer->m_legId) * connectorInfo->legMatrix;
	QPointF p1 = matrix.map(connectorInfo->legLine.p1());
	QPointF p2 = matrix.map(connectorInfo->legLine.p2());

	double x1 = p1.x() * defaultSizeF.width() / viewBox.width();
	double y1 = p1.y() * defaultSizeF.height() / viewBox.height();
	double x2 = p2.x() * defaultSizeF.width() / viewBox.width();
	double y2 = p2.y() * defaultSizeF.height() / viewBox.height();
	QPointF center = viewBox.center();
	double d1 = ((x1 - center.x()) * (x1 - center.x())) + ((y1 - center.y()) * (y1 - center.y()));
	double d2 = ((x2 - center.x()) * (x2 - center.x())) + ((y2 - center.y()) * (y1 - center.y()));

	// find the end which is closer to the center of the viewBox (which shouldn't include the leg)
	if (d1 <= d2) {
		svgIdLayer->m_legLine = QLineF(x1, y1, x2, y2);
	}
	else {
		svgIdLayer->m_legLine = QLineF(x2, y2, x1, y1);
	}
}

QPointF FSvgRenderer::calcTerminalPoint(const QString & terminalId, const QRectF & connectorRect, bool ignoreTerminalPoint, const QRectF & viewBox, QMatrix & terminalMatrix)
{
	Q_UNUSED(terminalMatrix);
	QPointF terminalPoint = connectorRect.center() - connectorRect.topLeft();    // default spot is centered
	if (ignoreTerminalPoint) {
		return terminalPoint;
	}
	if (terminalId.isNull() || terminalId.isEmpty()) {
		return terminalPoint;
	}

	QRectF tBounds = this->boundsOnElement(terminalId);
	if (tBounds.isNull()) {
		return terminalPoint;
	}

	QSizeF defaultSizeF = this->defaultSizeF();
	if (tBounds.width() >= defaultSizeF.width() && tBounds.height() >= defaultSizeF.height()) {
		return terminalPoint;
	}

	//DebugDialog::debug(	QString("terminal %5 rect %1,%2,%3,%4").arg(tBounds.x()).
										//arg(tBounds.y()).
										//arg(tBounds.width()).
										//arg(tBounds.height()).
										//arg(terminalID) );


	// matrixForElement only grabs parent matrices, not any transforms in the element itself
	//QMatrix tMatrix = this->matrixForElement(terminalId) ; //* terminalMatrix;
	//QRectF terminalRect = tMatrix.mapRect(tBounds);
	QRectF terminalRect = this->matrixForElement(terminalId).mapRect(tBounds);
	QPointF c = terminalRect.center();
	QPointF q(c.x() * defaultSizeF.width() / viewBox.width(), c.y() * defaultSizeF.height() / viewBox.height());
	terminalPoint = q - connectorRect.topLeft();
	//DebugDialog::debug(	QString("terminalagain %3 rect %1,%2 ").arg(terminalPoint.x()).
										//arg(terminalPoint.y()).
										//arg(terminalID) );

	return terminalPoint;
}

QList<SvgIdLayer *> FSvgRenderer::setUpNonConnectors() {

	QList<SvgIdLayer *> list;
	if (m_nonConnectorInfoHash.count() == 0) return list;

	foreach (QString nonConnectorID, m_nonConnectorInfoHash.keys()) {
		SvgIdLayer * svgIdLayer = new SvgIdLayer(ViewLayer::PCBView);
		svgIdLayer->m_processed = true;
		svgIdLayer->m_svgId = nonConnectorID;
		QRectF bounds = this->boundsOnElement(nonConnectorID);
		if (bounds.isNull()) {
			delete svgIdLayer;
			continue;
		}

		QSizeF defaultSizeF = this->defaultSizeF();
		QSize defaultSize = this->defaultSize();
		if ((bounds.width()) == defaultSizeF.width() && (bounds.height()) == defaultSizeF.height()) {
			delete svgIdLayer;
			continue;
		}

		QRectF viewBox = this->viewBoxF();

		ConnectorInfo * connectorInfo = m_nonConnectorInfoHash.value(nonConnectorID, NULL);		
		if (connectorInfo && connectorInfo->gotCircle) {
			svgIdLayer->m_radius = connectorInfo->radius * defaultSizeF.width() / viewBox.width();
			svgIdLayer->m_strokeWidth = connectorInfo->strokeWidth * defaultSizeF.width() / viewBox.width();
			//bounds = connectorInfo->cbounds;
		}

		// matrixForElement only grabs parent matrices, not any transforms in the element itself
		//QMatrix matrix0 = connectorInfo->matrix * this->matrixForElement(nonConnectorID);  
		//QRectF r1 = matrix0.mapRect(bounds);
		QRectF r1 = this->matrixForElement(nonConnectorID).mapRect(bounds);
		svgIdLayer->m_rect.setRect(r1.x() * defaultSize.width() / viewBox.width(), r1.y() * defaultSize.height() / viewBox.height(), r1.width() * defaultSize.width() / viewBox.width(), r1.height() * defaultSize.height() / viewBox.height());
		svgIdLayer->m_point = svgIdLayer->m_rect.center() - svgIdLayer->m_rect.topLeft();
		svgIdLayer->m_svgVisible = true;

		list.append(svgIdLayer);
	}

	return list;

}


