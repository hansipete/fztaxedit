/*******************************************************************

Part of the Fritzing project - http://fritzing.org
Copyright (c) 2007-2010 Fachhochschule Potsdam - http://fh-potsdam.de

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

$Revision: 6417 $:
$Author: cohen@irascible.com $:
$Date: 2012-09-14 23:34:09 +0200 (Fri, 14 Sep 2012) $

********************************************************************/



#include <QFrame>
#include <QSplitter>
#include <QVBoxLayout>

#include "partconnectorswidget.h"
#include "../utils/misc.h"

PartConnectorsWidget::PartConnectorsWidget(QWidget* info, QWidget *parent) : QFrame(parent) {
	QVBoxLayout *layout = new QVBoxLayout(this);
	layout->addWidget(info);
	layout->setMargin(4);
	layout->setSpacing(10);
}
