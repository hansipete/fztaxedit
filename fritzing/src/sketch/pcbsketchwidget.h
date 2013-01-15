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

$Revision: 6742 $:
$Author: irascibl@gmail.com $:
$Date: 2013-01-01 10:06:53 +0100 (Tue, 01 Jan 2013) $

********************************************************************/

#ifndef PCBSKETCHWIDGET_H
#define PCBSKETCHWIDGET_H

#include "sketchwidget.h"
#include <QVector>

class PCBSketchWidget : public SketchWidget
{
	Q_OBJECT

public:
    PCBSketchWidget(ViewLayer::ViewIdentifier, QWidget *parent=0);

	void addViewLayers();
	bool canDeleteItem(QGraphicsItem * item, int count);
	bool canCopyItem(QGraphicsItem * item, int count);
	void createTrace(Wire *);
	void excludeFromAutoroute(bool exclude);
	void selectAllExcludedTraces();
	void selectAllIncludedTraces();
	bool hasAnyNets();
	void forwardRoutingStatus(const RoutingStatus &);
	void addDefaultParts();
	void showEvent(QShowEvent * event);
	void initWire(Wire *, int penWidth);
	virtual bool autorouteTypePCB();
	virtual double getKeepout();
	virtual const QString & traceColor(ConnectorItem *);
	const QString & traceColor(ViewLayer::ViewLayerSpec);
	virtual void ensureTraceLayersVisible();
	virtual void ensureTraceLayerVisible();
	bool canChainMultiple();
	void setNewPartVisible(ItemBase *);
	void setClipEnds(class ClipableWire *, bool);
	void showGroundTraces(QList<ConnectorItem *> & seeds, bool show);
    virtual double getLabelFontSizeTiny();
	virtual double getLabelFontSizeSmall();
	virtual double getLabelFontSizeMedium();
	virtual double getLabelFontSizeLarge();
	ViewLayer::ViewLayerID getWireViewLayerID(const ViewGeometry & viewGeometry, ViewLayer::ViewLayerSpec);
	QList<ItemBase *> findBoard();
    ItemBase * findSelectedBoard(int & boardCount);
    ItemBase * findBoardBeneath(ItemBase *);
	double getRatsnestOpacity();
	double getRatsnestWidth();

	void setBoardLayers(int, bool redraw);
    void swapLayers(ItemBase * itemBase, int newLayers, QUndoCommand * parentCommand);
	void loadFromModelParts(QList<ModelPart *> & modelParts, BaseCommand::CrossViewType, QUndoCommand * parentCommand, 
							bool offsetPaste, const QRectF * boundingRect, bool seekOutsideConnections, QList<long> & newIDs);
	virtual bool isInLayers(ConnectorItem *, ViewLayer::ViewLayerSpec);
	bool routeBothSides();
	bool sameElectricalLayer2(ViewLayer::ViewLayerID, ViewLayer::ViewLayerID);
	void changeTraceLayer(bool force, QUndoCommand * parentCommand);
	void changeLayer(long id, double z, ViewLayer::ViewLayerID viewLayerID);
	void updateNet(Wire*);
	bool acceptsTrace(const ViewGeometry & viewGeometry);
	ItemBase * placePartDroppedInOtherView(ModelPart *, ViewLayer::ViewLayerSpec, const ViewGeometry & viewGeometry, long id, SketchWidget * dropOrigin);
	void autorouterSettings();
	void getViaSize(double & ringThickness, double & holeSize);
    void deleteItem(ItemBase *, bool deleteModelPart, bool doEmit, bool later);
	double getTraceWidth();
	virtual double getAutorouterTraceWidth();
	void getBendpointWidths(class Wire *, double w, double & w1, double & w2, bool & negativeOffsetRect);
	double getSmallerTraceWidth(double minDim);
	bool groundFill(bool fillGroundTraces, ViewLayer::ViewLayerID, QUndoCommand * parentCommand);
	void setGroundFillSeeds();
	void clearGroundFillSeeds();
	QString generateCopperFillUnit(ItemBase * itemBase, QPointF whereToStart);
	double getWireStrokeWidth(Wire *, double wireWidth);
	ItemBase * addCopperLogoItem(ViewLayer::ViewLayerSpec viewLayerSpec);
	QString characterizeGroundFill(ViewLayer::ViewLayerID);
	ViewGeometry::WireFlag getTraceFlag();
	void hideCopperLogoItems(QList<ItemBase *> &);
	void restoreCopperLogoItems(QList<ItemBase *> &);
	void hideHoles(QList<ItemBase *> &);
    QString makePasteMask(const QString & svgMask, ItemBase * board, double dpi, const LayerList & maskLayerIDs);
	int selectAllItemType(ModelPart::ItemType, const QString & typeName);
	bool isBoardLayerChange(ItemBase * itemBase, const QString & newModuleID, int & newLayers);
	void convertToVia(ConnectorItem * lastHoverEnterConnectorItem);
	void convertToBendpoint();
    int checkLoadedTraces();
	bool hasCustomBoardShape();
    virtual QSizeF jumperItemSize();
    virtual LayerList routingLayers(ViewLayer::ViewLayerSpec);
    virtual bool attachedToTopLayer(ConnectorItem *);
    virtual bool attachedToBottomLayer(ConnectorItem *);
    QHash<QString, QString> getAutorouterSettings();
    void setAutorouterSettings(QHash<QString, QString> &);
	void getDefaultViaSize(QString & ringThickness, QString & holeSize);

public slots:
	void resizeBoard(double w, double h, bool doEmit);
	void showLabelFirstTime(long itemID, bool show, bool doEmit);
	void changeBoardLayers(int layers, bool doEmit);


public:
	enum CleanType {
		noClean,
		ninetyClean
	};

	CleanType cleanType();

protected:
	void setWireVisible(Wire * wire);
	// void checkAutorouted();
	ViewLayer::ViewLayerID multiLayerGetViewLayerID(ModelPart * modelPart, ViewLayer::ViewIdentifier, ViewLayer::ViewLayerSpec, LayerList &);
	bool canChainWire(Wire *);
	bool canDragWire(Wire * wire);
	const QString & hoverEnterPartConnectorMessage(QGraphicsSceneHoverEvent * event, ConnectorItem * item);
	//bool modifyNewWireConnections(Wire * dragWire, ConnectorItem * fromOnWire, ConnectorItem * from, ConnectorItem * to, QUndoCommand * parentCommand);
	ViewLayer::ViewLayerID getDragWireViewLayerID(ConnectorItem *);
	bool canDropModelPart(ModelPart * modelPart);
	bool canCreateWire(Wire * dragWire, ConnectorItem * from, ConnectorItem * to);
	bool bothEndsConnected(Wire * wire, ViewGeometry::WireFlags, ConnectorItem * oneEnd, QList<Wire *> & wires, QList<ConnectorItem *> & partConnectorItems);
	void setUpColor(ConnectorItem * fromConnectorItem, ConnectorItem * toConnectorItem, Wire * wire, QUndoCommand * parentCommand);
	ConnectorItem * findNearestPartConnectorItem(ConnectorItem * fromConnectorItem);
	bool bothEndsConnectedAux(Wire * wire, ViewGeometry::WireFlags flag, ConnectorItem * oneEnd, QList<Wire *> & wires, QList<ConnectorItem *> & partConnectorItems, QList<Wire *> & visited);
	void getLabelFont(QFont &, QColor &, ViewLayer::ViewLayerSpec);
	double defaultGridSizeInches();
	ViewLayer::ViewLayerID getLabelViewLayerID(ViewLayer::ViewLayerSpec);
	ViewLayer::ViewLayerSpec wireViewLayerSpec(ConnectorItem *);
	bool resizingJumperItemPress(QGraphicsItem * item);
	bool resizingJumperItemRelease();
	void resizeJumperItem();
	QPoint calcFixedToCenterItemOffset(const QRect & viewPortRect, const QSizeF & helpSize);
	void dealWithDefaultParts();
	bool connectorItemHasSpec(ConnectorItem * connectorItem, ViewLayer::ViewLayerSpec spec);
	ViewLayer::ViewLayerSpec createWireViewLayerSpec(ConnectorItem * from, ConnectorItem * to);
	Wire * createTempWireForDragging(Wire * fromWire, ModelPart * wireModel, ConnectorItem * connectorItem, ViewGeometry & viewGeometry, ViewLayer::ViewLayerSpec);
	void prereleaseTempWireForDragging(Wire*);
	void rotatePartLabels(double degrees, QTransform &, QPointF center, QUndoCommand * parentCommand);
	bool hasNeighbor(ConnectorItem * connectorItem, ViewLayer::ViewLayerID viewLayerID, const QRectF & r);
	void setGroundFillSeeds(const QString & intro);
	bool collectGroundFillSeeds(QList<ConnectorItem *> & seeds, bool includePotential);
	void shiftHoles();
	void selectAllXTraces(bool autoroutable, const QString & cmdText, bool forPCB);
	bool canAlignToCenter(ItemBase *);
    void selectAllWires(ViewGeometry::WireFlag flag);
    QString checkDroppedModuleID(const QString & moduleID);
    bool canConnect(Wire * from, ItemBase * to);
	void collectThroughHole(QList<ConnectorItem *> & th, QList<ConnectorItem *> & pads, const LayerList &);
	ViewLayer::ViewLayerSpec getViewLayerSpec(ModelPart * modelPart, QDomElement & instance, QDomElement & view, ViewGeometry &);

signals:
	void subSwapSignal(SketchWidget *, ItemBase *, const QString & newModuleID, ViewLayer::ViewLayerSpec, long & newID, QUndoCommand * parentCommand);
	void boardDeletedSignal();
	void groundFillSignal();
	void copperFillSignal();

protected:
	static void calcDistances(Wire * wire, QList<ConnectorItem *> & ends);
	static void clearDistances();
	static int calcDistance(Wire * wire, ConnectorItem * end, int distance, QList<Wire *> & distanceWires, bool & fromConnector0);
	static int calcDistanceAux(ConnectorItem * from, ConnectorItem * to, int distance, QList<Wire *> & distanceWires);

protected slots:
	void alignJumperItem(class JumperItem *, QPointF &);
	void wireSplitSlot(class Wire*, QPointF newPos, QPointF oldPos, QLineF oldLine);
	void postImageSlot(class GroundPlaneGenerator *, QImage * image, QGraphicsItem * board, QList<QRectF> &);

protected:
	CleanType m_cleanType;
	QPointF m_jumperDragOffset;
	QPointer<class JumperItem> m_resizingJumperItem;
	QList<ConnectorItem *> * m_groundFillSeeds;
    QHash<QString, QString> m_autorouterSettings;

protected:
	static QSizeF m_jumperItemSize;
	static const char * FakeTraceProperty;
};

#endif
