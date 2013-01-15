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

$Revision: 6466 $:
$Author: irascibl@gmail.com $:
$Date: 2012-09-22 13:22:33 +0200 (Sat, 22 Sep 2012) $

********************************************************************/

#ifndef PESVGVIEW_H
#define PESVGVIEW_H

#include <QFrame>
#include <QRadioButton>
#include <QPushButton>
#include <QVBoxLayout>
#include <QGroupBox>
#include <QCheckBox>
#include <QLabel>
#include <QListWidget>
#include <QDomDocument>
#include <QDoubleSpinBox>

class PESvgView : public QWidget
{
Q_OBJECT
public:
	PESvgView(QWidget * parent = NULL);
	~PESvgView();

    void highlightElement(class PEGraphicsItem *);
	void setChildrenVisible(bool vis);
    void setFilename(const QString &);

protected:
    QLabel * m_svgElement;
    QLabel * m_height;
    QLabel * m_width;
    QLabel * m_x;
    QLabel * m_y;
    QLabel * m_filename;
    QLabel * m_units;
    class PEGraphicsItem * m_pegi;

};

#endif
