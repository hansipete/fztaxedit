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

#ifndef FSVGRENDERER_H
#define FSVGRENDERER_H

#include <QHash>
#include <QSvgRenderer>
#include <QXmlStreamReader>
#include <QDomDocument>
#include <QMatrix>
#include <QStringList>

#include "viewlayer.h"

struct ConnectorInfo {
	bool gotCircle;
	double radius;
	double strokeWidth;
	QMatrix matrix;
	//QRectF cbounds;
	QMatrix terminalMatrix;
	QMatrix legMatrix;
	QString legColor;
	QLineF legLine;
	double legStrokeWidth;
    bool gotPath;
};

typedef QHash<ViewLayer::ViewLayerID, class FSvgRenderer *> RendererHash;

class FSvgRenderer : public QSvgRenderer
{
Q_OBJECT
public:
	FSvgRenderer(QObject * parent = 0);
	~FSvgRenderer();

	QByteArray loadSvg(const QString & filename, const QStringList & connectorIDs, const QStringList & terminalIDs, const QStringList & legIDs, const QString & setColor, const QString & colorElementID, bool findNonConnectors);
	QByteArray loadSvg(const QString & filename);
	QByteArray loadSvg( const QByteArray & contents, const QString & filename, const QStringList & connectorIDs, const QStringList & terminalIDs, const QStringList & legIDs, const QString & setColor, const QString & colorElementID, bool findNonConnectors);     // for SvgSplitter loads
	QByteArray loadSvg( const QByteArray & contents, const QString & filename, bool findNonConnectors);						// for SvgSplitter loads
	bool loadSvgString(const QString & svg);
	bool loadSvgString(const QString & svg, QString & newSvg);
	bool fastLoad(const QByteArray & contents);								
	const QString & filename();
	QSizeF defaultSizeF();
	bool setUpConnector(struct SvgIdLayer * svgIdLayer, bool ignoreTerminalPoint);
	QList<SvgIdLayer *> setUpNonConnectors();

public:
	static void cleanup();
	static QSizeF parseForWidthAndHeight(QXmlStreamReader &);
	static QPixmap * getPixmap(QSvgRenderer * renderer, QSize size);
    static void initNames();

protected:
	bool determineDefaultSize(QXmlStreamReader &);
	QByteArray loadAux (const QByteArray & contents, const QString & filename, const QStringList & connectorIDs, const QStringList & terminalIDs, const QStringList & legIDs, const QString & setColor, const QString & colorElementID, bool findNonConnectors);
	bool initConnectorInfo(QDomDocument &, const QStringList & connectorIDs, const QStringList & terminalIDs, const QStringList & legIDs, const QString & filename);
	ConnectorInfo * initConnectorInfoStruct(QDomElement & connectorElement, const QString & filename);
	bool initConnectorInfoStructAux(QDomElement &, ConnectorInfo * connectorInfo, const QString & filename);
	void initNonConnectorInfo(QDomDocument & domDocument, const QString & filename);
	void initNonConnectorInfoAux(QDomElement & element, const QString & filename);
	void initTerminalInfoAux(QDomElement & element, const QStringList & connectorIDs, const QStringList & terminalIDs);
	void initLegInfoAux(QDomElement & element, const QStringList & connectorIDs, const QStringList & legIDs, bool & gotOne);
	void initConnectorInfoAux(QDomElement & element, const QStringList & connectorIDs, const QString & filename);
	QPointF calcTerminalPoint(const QString & terminalId, const QRectF & connectorRect, bool ignoreTerminalPoint, const QRectF & viewBox, QMatrix & terminalMatrix);
	bool initLegInfoAux(QDomElement & element, ConnectorInfo * connectorInfo);
	void calcLeg(SvgIdLayer *, const QRectF & viewBox, ConnectorInfo * connectorInfo);
	ConnectorInfo * getConnectorInfo(const QString & connectorID);
	void clearConnectorInfoHash(QHash<QString, ConnectorInfo *> & hash);

protected:
	QString m_filename;
	QSizeF m_defaultSizeF;
	QHash<QString, ConnectorInfo *> m_connectorInfoHash;
	QHash<QString, ConnectorInfo *> m_nonConnectorInfoHash;

public:
	static QString NonConnectorName;

};


#endif
