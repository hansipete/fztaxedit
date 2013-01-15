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



#ifndef EDITABLELABEL_H_
#define EDITABLELABEL_H_

#include <QWidget>
#include <QLabel>

class EditableLabel : public QLabel {
	Q_OBJECT

	public:
		EditableLabel(QWidget *parent=0);
		EditableLabel(const QString & text, QWidget *parent=0);

	public slots:
		void editionCompleted(QString newText);

	signals:
		void editionStarted(QString);

	protected:
		void mousePressEvent(QMouseEvent *event);
};

#endif /* EDITABLELABEL_H_ */
